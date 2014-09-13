Full Disclosure vs. Coordinated Disclosure
######################################################
:slug: full-disclosure-vs-coordinated-disclosure
:author: Taylor Hornby
:date: 2014-09-12 00:00
:category: security
:tags: disclosure

With the exception of the AntiSec movement, most researchers agree that
vulnerabilities and exploits should be published *eventually* so that we can
learn from them. But *when* should we publish them?

Those who practice "Coordinated Disclosure" notify vendors of vulnerabilities
privately, giving them time to patch their software and send out an update. They
argue that keeping the vulnerability secret from the public makes it less likely
that the users running the vulnerable software will be exploited before a patch
is released.

Proponents of "Full Disclosure" argue that vulnerabilities (and even exploits)
should be published as widely as possible as soon as they are found. One reason
is that giving advance notice to any set of people is a risk. People who know
about the vulnerability can exploit it while users are still in the dark. When
vulnerabilities are disclosed immediately, users can at least make the concious
decision to stop using the software until a patch is released. In economic
terms, users who don't know about a vulnerability can't user their wallets
pressure the vendor into developing a patch, and as a result vendors might leave
users vulnerable while they procrastinate the development of a patch.

There are many more arguments and counter-arguments on both sides. It's not
always clear which practice is best. It usually depends on the specific case.
But in all cases, the right option is the one that *reduces the actual count of
malicious exploitation*. This is something that could be tested empirically for
different classes of vulnerability.
