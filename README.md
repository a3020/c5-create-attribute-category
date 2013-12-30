Concrete5 - create new attribute category
============================

PHP-script to generate classes for a new concrete5 attribute category.

**Usage:**
php create.php --at-name-lowercase=blog_post --at-name-camelcase=BlogPost --table-name-camelcase=BlogPosts
	
**Optional parameters:**
--p-handle (package handle. Default: false)
--at-id (table identifier, e.g. blogID. Default: itemID)
	
**Additional information:**
You need to run this file from the command line and provide at least three parameters. The package handle is optional, but recommended. Don't use names such as collection, page, file, etc. as they will likely interfere with existing models.	

**More information:** [http://www.adrikodde.nl/blog/2013/concrete5-creating-attribute-categories/](http://www.adrikodde.nl/blog/2013/concrete5-creating-attribute-categories/)