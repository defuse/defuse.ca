function sendMessage()
{
    try {
        var message = document.getElementById("message").value;

        try {
            var present = nacl.box.keyPair();
            var future = nacl.box.keyPair();
        } catch (e) {
            alert("Sorry, it looks like your web browser can't generate random numbers. Try a different browser!");
            return;
        }

        var encrypted_message = nacl.box(
            nacl.util.decodeUTF8(message),
            nacl.util.decodeUTF8("000000000000000000000000"),
            future.publicKey,
            present.secretKey
        );

        document.getElementById("algorithm").value = "tweetnacl-js-x25519-xsalsa20-poly1305-zerononce";
        document.getElementById("present_public_key").value = nacl.util.encodeBase64(present.publicKey);
        document.getElementById("future_public_key").value = nacl.util.encodeBase64(future.publicKey);
        document.getElementById("message").value = nacl.util.encodeBase64(encrypted_message);
        document.getElementById("message").disabled = true;

        // Self-test decryption.
        var decrypted_message = nacl.util.encodeUTF8(nacl.box.open(
            nacl.util.decodeBase64(document.getElementById("message").value),
            nacl.util.decodeUTF8("000000000000000000000000"),
            nacl.util.decodeBase64(document.getElementById("present_public_key").value),
            future.secretKey
        ));

        if (message === decrypted_message) {
            document.getElementById("messageform").submit();
        } else {
            alert("Sorry, something is broken. =(");
            return;
        }
    } catch (e) {
        alert("Sorry, something is broken. =(");
    }
}
