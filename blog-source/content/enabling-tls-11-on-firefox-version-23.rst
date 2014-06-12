Enabling TLS v1.1 on Firefox 23
#################################
:slug: enabling-tls-11-on-firefox-version-23
:author: Taylor Hornby
:date: 2013-08-09 00:00
:category: security
:tags: firefox

Change the following settings in Firefox's about:config to enable TLS v1.1.


.. image:: https://defuse.ca/images/ff23-tls.png
    :alt: Enabling TLS 1.1 in Firefox 23

**Steps**

1. Open a new tab and type about:config into the address bar.
2. Search for tls.version.
3. Set security.tls.version.max to 2. This enables TLS 1.1.
4. Set security.tls.version.min to 1. This disables SSL 3.0.

Be aware that setting tls.version.max to 3 will enable TLS v1.2 in a future
release of Firefox, but setting it to 3 in version 23 actually makes it fall
back to using *only* TLS 1.0. So for now, set it to 2.

The image was contributed by `@voodooKobra`_.

.. _`@voodooKobra`: https://twitter.com/voodooKobra

