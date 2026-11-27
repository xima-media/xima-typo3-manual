..  _multiple-manuals:

================
Multiple manuals
================

Create one top-level manual page tree per manual, each with its own site
configuration and the ``xima/manual`` dependency. Use distinct site bases and
clear root-page titles. Typical divisions are one manual per website or per
editor role.

The backend module shows a manual selector when several manuals are accessible.
It remembers the current user's last selected manual; otherwise it chooses an
accessible one. Related-record links are grouped by manual, and coverage is
calculated for the selected manual only.

The primary colour, display mode, FAQ and changelog links are site settings, so
each manual can have different values. Languages are also configured per site.

Grant page access for each intended reader group. The backend registry excludes
manuals whose roots the user cannot read. This selection is a backend convenience,
not frontend authorization: a separate site is not automatically a private site.
See :ref:`access`.
