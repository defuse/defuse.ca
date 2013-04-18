# Makefile for compiling the GnuTLS pre-shared key client-server example.

server: server.c
	gcc -Wall -Werror -c server.c -o server.o `pkg-config gnutls --cflags`
	gcc server.o -o server `pkg-config gnutls --libs`

client: client.c
	gcc -Wall -Werror -c client.c -o client.o `pkg-config gnutls --cflags`
	gcc client.o -o client `pkg-config gnutls --libs`

