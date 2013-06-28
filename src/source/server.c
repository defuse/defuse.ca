//             GnuTLS pre-shared key (PSK) client-server example.
//                    This is the SERVER (server.c).
//
// This code was written by Taylor Hornby on April 18, 2013. You can find it
// on the web at:
//          https://defuse.ca/gnutls-psk-client-server-example.htm
// 
// This code is in the public domain. You can do whatever you want with it.
//
// In this simple example,
//   1. The server accepts a TCP connection from the client.
//   2. The server and client perform the SSL/TLS handshake over the connection.
//   3. The server sends the client some data.
//   4. The server and client close the SSL/TLS and TCP connection.
//   5. The server and the client exit.
//
// The code is heavily commented so that it can be understood by someone who has
// never used GnuTLS before. The GnuTLS manual can be found here:
//               http://www.gnutls.org/manual/gnutls.html

// Standard Library stuff
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>

// Stuff for sockets.
#include <unistd.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <sys/types.h>
#include <sys/socket.h>

// GnuTLS!
#include <gnutls/gnutls.h>

// This is the secret key that must be shared (over a secure channel!) between
// the client and server. Obviously in a real application it should be in
// a configuration file or something and not built-in constant. It also
// shouldn't be an ASCII string. Use a good CSPRNG!
#define SECRET_KEY "THIS IS THE PRE-SHARED KEY."
// This is the port number that the server will listen on.
#define PORT 8050
// GnuTLS log level. 9 is the most verbose.
#define LOG_LEVEL 0

int accept_one_connection(int port);
void error_exit(const char *msg);
// GnuTLS callbacks.
int psk_creds(gnutls_session_t, const char*, gnutls_datum_t*);
ssize_t data_push(gnutls_transport_ptr_t, const void*, size_t);
ssize_t data_pull(gnutls_transport_ptr_t, void*, size_t);
void print_logs(int, const char*);
void print_audit_logs(gnutls_session_t, const char*);

int main(int argc, char **argv)
{
    // We use the 'res' variable to capture return values, so we can check for
    // success/failure.
    int res;

    // Initialize GnuTLS's global state. You only have to do this once.
    gnutls_global_init();

    // Enable logging (for debugging).
    // Be careful with this in a real application. You don't want to reveal the
    // logging information to any potential attackers.
    gnutls_global_set_log_level(LOG_LEVEL);
    gnutls_global_set_log_function(print_logs);
    // Enable logging (for auditing).
    // (commented out because it's not available in my version of GnuTLS)
    // gnutls_global_set_audit_log_function(print_audit_logs);

    // A GnuTLS session is like a socket for an SSL/TLS connection.
    gnutls_session_t session;

    // Initialize the session for our connection from the client.
    res = gnutls_init(&session, GNUTLS_SERVER);
    if (res != GNUTLS_E_SUCCESS) {
        error_exit("gnutls_init() failed.\n");
    }

    // Allocate the structure we use to tell GnuTLS what the credentials
    // (pre-shared key) are.
    gnutls_psk_server_credentials_t cred;
    res = gnutls_psk_allocate_server_credentials(&cred);
    if (res != 0) {
        error_exit("gnutls_psk_allocate_server_credentials() failed.\n");
    }
    // GnuTLS will call psk_creds to ask for the key associated with the
    // client's username.
    gnutls_psk_set_server_credentials_function(cred, psk_creds);
    // Pass the "credentials" to the GnuTLS session. GnuTLS does NOT make an
    // internal copy of the information, so we have to keep the 'cred' structure
    // in memory (and not modify it) until we're done with this session.
    res = gnutls_credentials_set(session, GNUTLS_CRD_PSK, cred);
    if (res != 0) {
        error_exit("gnutls_credentials_set() failed.\n");
    }

    // Set the cipher suite priorities.
    // See section 6.10 of the GnuTLS manuals for a description of this string.
    // Run `gnutls-cli -l` to see what your GnuTLS supports.
    const char *error = NULL;
    // Here we allow all 128+ bit ciphers except RC4 and disable SSL3 and TLS1.0.
    res = gnutls_priority_set_direct(
        session,
        "SECURE128:-VERS-SSL3.0:-VERS-TLS1.0:-ARCFOUR-128:+PSK:+DHE-PSK",
        &error
    );
    if (res != GNUTLS_E_SUCCESS) {
        error_exit("gnutls_priority_set_direct() failed.\n");
    }

    // Accept a TCP connection.
    int connfd = accept_one_connection(PORT);

    // Below we give GnuTLS access to the transport layer. GnuTLS needs a way of
    // reading and writing to and from the TCP socket (or whatever transport
    // layer you're using). Newer versions of GnuTLS can work directly with
    // Berkely sockets (see gnutls_transport_set_int()), but here we use the
    // push/pull callbacks as an example.

    // GnuTLS passes a pointer to the send and receive callbacks. Here we
    // construct a pointer to the connection's file descriptor which we will
    // tell GnuTLS to pass to the callbacks.
    int *connfdPtr = malloc(sizeof(int));
    *connfdPtr = connfd;
    gnutls_transport_set_ptr(session, connfdPtr);

    // Set the callback that allows GnuTLS to PUSH data TO the transport layer
    gnutls_transport_set_push_function(session, data_push);
    // Set the callback that allows GnuTls to PULL data FROM the tranport layer
    gnutls_transport_set_pull_function(session, data_pull);

    // Now we perform the actual SSL/TLS handshake.
    // If you wanted to, you could send some data over the tcp socket before
    // giving it to GnuTLS and performing the handshake. See the GnuTLS manual
    // on STARTTLS for more information.
    do {
        res = gnutls_handshake(session);
        // GnuTLS manual says to keep trying until it returns zero (success) or
        // encounters a fatal error.
    } while ( res != 0 && !gnutls_error_is_fatal(res) );
    // If there is a fatal error, it is a fatal error for the PROTOCOL, not
    // a fatal error for the GnuTLS library. In this example, we just exit. In
    // a real application, you would alert the user, try again, etc.
    if (gnutls_error_is_fatal(res)) {
        error_exit("Fatal error during handshake.\n");
    }

    // If all went well, we've established a secure connection to the client.
    // We can now send some data.
    int i;
    char buf[100];
    for (i = 1; i <= 10; i++) {
        sprintf(buf, "Server %d\r\n", i);
        do {
            // gnutls_record_send() behaves like send(), so it doesn't always
            // send all of the available data. You should check the return value
            // and send anything it didn't send (just like you would with
            // send()).
            res = gnutls_record_send(session, buf, strlen(buf));
        } while (res == GNUTLS_E_INTERRUPTED || res == GNUTLS_E_AGAIN);
        if (gnutls_error_is_fatal(res)) {
            // Again, a fatal error doesn't mean you have to exit, it's just
            // a fatal error for the protocol. You should alert the user, retry,
            // etc.
            error_exit("Fatal error during send.\n");
        }
    }

    // Tear down the SSL/TLS connection. You could just close the TCP socket,
    // but this authenticates to the client your intent to close the connection,
    // so they can distinguish between you actually wanting to close the
    // connection and an attacker forcing it to close. Always do this.
    gnutls_bye(session, GNUTLS_SHUT_RDWR);

    // Destroy the session.
    gnutls_deinit(session);

    // Close the TCP connection.
    close(connfd);
    // GnuTLS shouldn't be making calls to the push/pull functions, so it's now
    // safe to deallocate the pointer to the socket.
    free(connfdPtr);

    // Destroy the structure we used to tell GnuTLS what credentials to use.
    // We have to do this after we call gnutls_deinit(), since GnuTLS doesn't
    // make an internal copy of the structure.
    gnutls_psk_free_server_credentials(cred);

    // Finally, tear down GnuTLS's global state.
    gnutls_global_deinit();

    printf("All done!\n");
    return 0;
}

// GnuTLS calls this function to get the pre-shared key. The client will tell
// the server its username, and GnuTLS will give us that username. We have to
// return the key that we share with that client. We set this callback with
// gnutls_psk_set_server_credentials_function().
int psk_creds(gnutls_session_t session, const char *username, gnutls_datum_t *key)
{
    // For this example, we ignore the username and return the same key every
    // time. In a real application, you would look up the key for the username
    // and return that. If the username does not exist, return a negative
    // number (see the manual).
    key->size = strlen(SECRET_KEY);
    key->data = gnutls_malloc(key->size);
    if (key->data == NULL) {
        return -1;
    }
    memcpy(key->data, SECRET_KEY, key->size);
    return 0;
}

// GnuTLS calls this function to send data through the transport layer. We set
// this callback with gnutls_transport_set_push_function(). It should behave
// like send() (see the manual for specifics).
ssize_t data_push(gnutls_transport_ptr_t ptr, const void* data, size_t len)
{
    int sockfd = *(int*)(ptr);
    return send(sockfd, data, len, 0);
}

// GnuTLS calls this function to receive data from the transport layer. We set
// this callback with gnutls_transport_set_pull_function(). It should act like
// recv() (see the manual for specifics).
ssize_t data_pull(gnutls_transport_ptr_t ptr, void* data, size_t maxlen)
{
    int sockfd = *(int*)(ptr);
    return recv(sockfd, data, maxlen, 0);
}

// GnuTLS will call this function whenever there is a new debugging log message.
void print_logs(int level, const char* msg)
{
    printf("GnuTLS [%d]: %s", level, msg);
}

// GnuTLS will call this function whenever there is a new audit log message.
void print_audit_logs(gnutls_session_t session, const char* message)
{
    printf("GnuTLS Audit: %s", message);
}

// Listens on 'port' for a TCP connection. Accepts at most one connection.
int accept_one_connection(int port) 
{
    int res;
    // Listen for a TCP connection.
    struct sockaddr_in serv_addr;
    int listenfd = socket(AF_INET, SOCK_STREAM, 0);
    if (listenfd < 0) {
        error_exit("socket() failed.\n");
    }
    int yes = 1;
    if (setsockopt(listenfd, SOL_SOCKET, SO_REUSEADDR, &yes, sizeof(int)) == -1) {
        error_exit("setsockopt() failed.\n");
    }
    memset(&serv_addr, 0, sizeof(serv_addr));
    serv_addr.sin_family = AF_INET;
    serv_addr.sin_addr.s_addr = htonl(INADDR_ANY);
    serv_addr.sin_port = htons(port);
    res = bind(listenfd, (struct sockaddr*)&serv_addr, sizeof(serv_addr));
    if (res < 0) {
        error_exit("bind() failed.\n");
    }
    res = listen(listenfd, 10);
    if (res < 0) {
        error_exit("listen() failed.\n");
    }

    printf("Waiting for a connection...\n");

    // Accept a TCP connection.
    int connfd = accept(listenfd, (struct sockaddr*)NULL, NULL);
    if (connfd < 0) {
        error_exit("accept() failed.\n");
    }

    printf("A client connected!\n");

    close(listenfd);

    return connfd;
}

void error_exit(const char *msg) 
{
    printf("ERROR: %s", msg);
    exit(1);
}
