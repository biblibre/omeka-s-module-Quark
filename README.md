# Quark (module for Omeka S)

This module generates ARK identifiers for Omeka S resources, like
[Ark](https://gitlab.com/Daniel-KM/Omeka-S-module-Ark).

## Comparison with the Ark module

Unlike [Ark](https://gitlab.com/Daniel-KM/Omeka-S-module-Ark), Quark:

* does not need an external library,
* does not need `dba` PHP extension,
* does not need a separate database file,
* uses [UUID](https://fr.wikipedia.org/wiki/Universally_unique_identifier) to
  generate unique identifiers
* has less configuration options
* does not implement "inflections" (URLs that end with one or two question marks) yet

Because it uses UUID, Quark should generate ARK identifiers faster that Ark,
especially as the number of resources grow.

## Installation

See general end user documentation for [Installing a module](http://omeka.org/s/docs/user-manual/modules/#installing-modules)

## License

The Quark source code is distributed under the GNU General Public License,
version 3 (GPLv3). The full text of this license is given in the license file.
