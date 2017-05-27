Language tool
#############

PHP CLI scripts to create, merge and crunch translation dictionaries of SunLight CMS.

.. contents::


Requirements
************

-  PHP 5.3+


What is a translation dictionary
********************************

Translation dictionary is a ``.php`` script that returns a simple entry => label map.


Usage
*****

.. WARNING::

   Make sure all files are using UTF-8 encoding.


Creating a new dictionary
=========================

Building an empty dictionary to start translating from scratch.

1. put current dictionaries in the ``template-current`` folder

   -  current dictionaries should be complete and valid dictionaries of the version you are creating
   -  they serve as a template

2. run the following from the console:

   -  ``php create.php``

3. the new dictionaries will be saved to the ``output`` folder (prefixed with "new.")

   -  all entries will be empty
   -  the original label will be in a PHP comment above each entry


Merging dictionaries
====================

Merging dictionaries is useful if you wish to update an existing dictionary to a newer version.

1. put input dictionaries in the ``input`` folder

   -  input dictionaries are the ones that you wish to update and merge

2. (optional) put old dictionaries in the ``template-old`` folder

   -  old dictionaries should be complete and valid dictionaries of the previous version
   -  they are used to detect renames and changes between the old and current version

3. put current dictionaries in the ``template-current`` folder

   -  current dictionaries should be complete and valid dictionaries of the newer version
   -  they serve as a template when merging

4. run the following from the console:

   -  ``php merge.php name1 [name2, name3, ...]``
   -  list the names (without extensions) of the input directories you wish to process

5. the merged dictionaries will be saved to the ``output`` folder (prefixed with "merged.")

   -  the new/changed entries (that need to be updated) will be empty
   -  the original label will be in a PHP comment above each entry


Crunching a dictionary
======================

Crunching a dictionary removes all PHP comments and reformats the PHP code. Do this when you have finished working on a dictionary.

1. put your complete dictionaries in the ``input`` folder
2. run the following from the console:

   -  ``php crunch.php name1 [name2, name3, ...]``
   -  list the names (without extensions) of the input directories you wish to crunch

3. the crunched dictionaries will be saved to the ``output`` folder (prefixed with "crunched.")
