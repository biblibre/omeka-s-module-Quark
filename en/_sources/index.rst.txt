Quark
=====

`Quark`_ is a module for Omeka S that generates ARK identifiers for Omeka S
resources, like `Ark`_.

Comparison with the Ark module
------------------------------

Unlike `Ark`_, Quark:

* does not need an external library,
* does not need ``dba`` PHP extension,
* does not need a separate database file,
* uses `UUID <https://fr.wikipedia.org/wiki/Universally_unique_identifier>`_ to
  generate unique identifiers
* has less configuration options
* does not implement "inflections" (URLs that end with one or two question
  marks) yet

Because it uses UUID, Quark should generate ARK identifiers faster that `Ark`_,
especially as the number of resources grow.

Installation
------------

See general end user documentation for `Installing a module
<http://omeka.org/s/docs/user-manual/modules/#installing-modules>`_.

.. _Quark: https://github.com/biblibre/omeka-s-module-Quark
.. _Ark: https://gitlab.com/Daniel-KM/Omeka-S-module-Ark

.. toctree::
   :maxdepth: 2
   :hidden:

   configuration
