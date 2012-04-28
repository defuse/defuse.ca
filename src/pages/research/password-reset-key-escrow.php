<h1>Modernizing Password Reset &amp; Key Escrow</h1>

<p>
Let me begin by saying that "password recovery" is a misnomer, as it implies the password is stored somewhere and is returned to the user when they forget it. 
We should be calling it "password reset," which implies a process that allows the user to change their password without providing the current one, and without losing data. 
A password is an authentication token from which <em>many</em> keys for many different purposes on many different systems may be derived. 
So passwords must be never be stored to enable password reset, not even in an encrypted database. Passwords must always be hashed with salt.
We may want to escrow the encryption keys, which may be derived from the password, but never the password itself. 
</p>

<p>
Password reset should be optional. 
It should be implemented on top of a secure system to deliberately but carefully weaken the system just enough to enable password reset. 
Systems that seem to include password reset "by default" are fundamentally less secure than ones where password reset is implemented as controlled key escrow on top of the secure system.
</p>

<p>
But the heart of the matter lies within the following questions:
</p>
<ol>
    <li>Who is responsible for remembering the user's keys and how do they protect them?</li>
    <li>How does the user, who has forgotten their keys (or the information needed to generate them), authenticate themselves to the escrow entity?</li>
</ol>

<p>
If the user doesn't trust the escrow entity or they can't strongly authenticate themselves, then obviously a high degree of security is impossible.
</p>



The most common solution is that the 

The method of authentication, an email loop, 
- ideal model would be pluggable escrow services
-  secret sharing among them!
-    secret sharing within escrow services
- email loop must also trust email provider
