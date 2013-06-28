//             GnuTLS pre-shared key (PSK) client-server example.
//                    This is the CLIENT (client.c).
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

// Standard library stuff
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>

// Stuff for sockets
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

// IPv4 address of the server which we will connect to.
#define SERVER_IP "127.0.0.1"
// The TCP port number that the server is running on, which we will connect to.
#define SERVER_PORT 8050
// GnuTLS log level. 9 is the most verbose.
#define LOG_LEVEL 0

int make_one_connection(const char *address, int port);
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

    // Initialize the session for our connection to the server.
    res = gnutls_init(&session, GNUTLS_CLIENT);
    if (res != GNUTLS_E_SUCCESS) {
        error_exit("gnutls_init() failed.\n");
    }

    // Allocate a structure that we use to tell GnuTLS what our "username" and
    // pre-shared key is.
    gnutls_psk_client_credentials_t cred;
    res = gnutls_psk_allocate_client_credentials(&cred);
    if (res != 0) {
        error_exit("gnutls_psk_allocate_client_credentials() failed.\n");
    }
    // Construct the pre-shared key in GnuTLS's 'datum' structure, whose
    // definition is as follows:
    //      typedef struct {
    //          unsigned char *data;
    //          unsigned int size;
    //      } gnutls_datum_t;
    gnutls_datum_t key;
    key.size = strlen(SECRET_KEY);
    key.data = malloc(key.size);
    memcpy(key.data, SECRET_KEY, key.size);
    // Put the username and key into the structure we use to tell GnuTLs what
    // the credentials are. The example server doesn't care about usernames, so
    // we use "Alice" here.
    res = gnutls_psk_set_client_credentials(cred, "Alice", &key, GNUTLS_PSK_KEY_RAW);
    memset(key.data, 0, key.size);
    free(key.data);
    key.data = NULL;
    key.size = 0;
    // You could instead use a callback to give the credentials to GnuTLS. See
    // gnutls_psk_set_client_credentials_function().
    if (res != 0) {
        error_exit("gnutls_psk_set_client_credentials() failed.\n");
    }
    // Pass our credentials (which contains the username and key) to GnuTLS.
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

    // Make a TCP connection to the server.
    int connfd = make_one_connection(SERVER_IP, SERVER_PORT);

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
    } while ( res != 0 && !gnutls_error_is_fatal(res) );

    if (gnutls_error_is_fatal(res)) {
        error_exit("Fatal error during handshake.\n");
    }

    // If the handshake worked, we can now receive the data that the server is
    // sending to us.
    printf("------- BEGIN DATA FROM SERVER -------\n");
    char buf[100];
    res = gnutls_record_recv(session, buf, sizeof(buf));
    while (res != 0) {
        if (res == GNUTLS_E_REHANDSHAKE) {
            error_exit("Peer wants to re-handshake but we don't support that.\n");
        } else if (gnutls_error_is_fatal(res)) {
            error_exit("Fatal error during read.\n");
        } else if (res > 0) {
            fwrite(buf, 1, res, stdout);
            fflush(stdout);
        }
        res = gnutls_record_recv(session, buf, sizeof(buf));
    }
    printf("------- END DATA FROM SERVER -------\n");

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
    gnutls_psk_free_client_credentials(cred);

    // Finally, tear down the GnuTLS's global state.
    gnutls_global_deinit();

    printf("All done!\n");
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

// Makes a TCP connection to the given IPv4 address and port number.
int make_one_connection(const char *address, int port) 
{
    int res;
    int connfd = socket(AF_INET, SOCK_STREAM, 0);
    struct sockaddr_in serv_addr;
    if (connfd < 0) {
        error_exit("socket() failed.\n");
    }
    memset(&serv_addr, 0, sizeof(serv_addr));
    serv_addr.sin_family = AF_INET;
    serv_addr.sin_port = htons(port);
    res = inet_pton(AF_INET, address, &serv_addr.sin_addr);
    if (res != 1) {
        error_exit("inet_pton() failed.\n");
    }
    res = connect(connfd, (struct sockaddr*)&serv_addr, sizeof(serv_addr));
    if (res < 0) {
        error_exit("connect() failed.\n");
    }
    return connfd;
}

void error_exit(const char *msg) 
{
    printf("ERROR: %s", msg);
    exit(1);
}
