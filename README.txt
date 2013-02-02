Table of Contents (TOC) filter
    Readme for version 2.


The Table of Contents filter scans text for heading tags
(h1-h4 in this version) and automatically generates a nested
list of these tags as a table of contents. Each link in the table
is made into an html link to the actual heading, and the heading
itself is given a backlink to the table of contents.

TODO
====

This version contains no configuration page - it is intended that
a future version will provide the ability for the user to modify
the various text elements added that are currently hardcoded.

CSS
===

CSS styling may be used to modify the appearance of the table. The entire
table is contained within a div with the 'toc' class. The backlinks have
the class 'toc_link' applied to them.
