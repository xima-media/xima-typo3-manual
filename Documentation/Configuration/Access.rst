..  _access:

============
Access rules
============

..  warning::
    The manual is a frontend site. Its base URL and printable page type are
    publicly reachable unless you protect them separately. Backend page
    permissions and the manual selector do not protect frontend URLs.

Backend readers
===============

Give non-admin users access to the :guilabel:`Editor Manual` module
(``xima_typo3_manual``) and read access to the manual pages through the usual
TYPO3 page permissions and database mounts. The module, selector, coverage report
and related-record links filter manuals by the current backend user's page
access. A manual the user cannot read is not offered.

Backend authors
===============

Additionally grant the required page/content editing permissions, the manual page
type (``701``), the four content types (``mtext``, ``mbox``, ``msteps``,
``mannotation``), and access to the files used in screenshots. The
``tx_ximatypo3manual_relations`` fields on ``pages`` and ``tt_content`` are
exclude fields: grant them to authors who maintain related-record assignments.

The preset installation endpoint requires an administrator. Hiding the wizard
from editors is not the only check; the endpoint checks this as well.

Private manuals
===============

Protect the manual site's base at the web server or reverse proxy, for example
with a network allowlist or HTTP authentication. Apply that protection to every
language URL and the printable page type too. Files in a public FAL storage may
still be fetched directly; include them in your protection plan.

Test access while logged out, not just through the backend. If the frontend is
protected, the PDF renderer must still be able to fetch its printable URL from
the server. Backend authentication for the download does not automatically
satisfy HTTP authentication or a proxy's allowlist.

See :ref:`pdf-export` for the separate PDF download checks.
