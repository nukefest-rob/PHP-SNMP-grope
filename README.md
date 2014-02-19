PHP-SNMP-grope
==============

PHP functions to grope SNMPv2 devices and parse/collate returned data.

This code collection is the output of a project I worked on to create an
SNMPv2-based network device "looking glass" while employed at a mid-size
American university.  Our network was comprised of hundreds of switches,
routers, access points, etcetera - mostly Cisco and Foundry (acquired by
Brocade in 2008).  Any devices that correctly implement (and not all
do!) RFC 1213 (https://tools.ietf.org/html/rfc1213) and use SNMPv2
should be queriable using the MIB-2 libraries.

PHP
===

Most of the code was written in the mid-2000's, a time when PHP was in a
transitional phase between being a strictly procedural language and its
more object-compatible current form.  If you struggled with any of
PHP4's "object oriented" aspects, I'm sure you'll have some sympathy for
my choice to stick with a procedural approach.  Regrets about the lack
of namespaces, etc.  If I ever have access to a network like
[University]'s again (or even a studly simulator), I'd love to rewrite
these functions in Python and PHP5.  Until then, even if you can't use
the code itself, at least reading the code comments might help you with
your own efforts.

LICENSE
=======

I chose GPLv3 for this repo because I couldn't have written it without
using, looking at, and learning from a lot of other people's Open Source
efforts.  It's all based on publicly accessible documents (MIBs - even
the vendor-specific ones are publicly available as of early 2014) and
doesn't use any clever language constructions or extensions.  In other
words, all I did was a whole lot of reading and typing.  Be a mensch -
pass on the opportunity to learn and develop.

