Concrete5 - create new attribute category
============================

PHP-script to generate classes for a new concrete5 attribute category.

**Usage:**
php create.php --ac_l=bs_option --ac_c=BsOption --table=BsOptions --p_handle=booking_system --ac_id=optionID
	
**Optional parameters:**
--p_handle (package handle. Default: false)
--at_id (table identifier, e.g. blogID. Default: itemID)
	
**Additional information:**
You need to run this file from the command line and provide at least three parameters. The package handle is optional, but recommended. Don't use names such as collection, page, file, etc. as they will likely interfere with existing models.	

**More information:** [http://www.adrikodde.nl/blog/2013/concrete5-creating-attribute-categories/](http://www.adrikodde.nl/blog/2013/concrete5-creating-attribute-categories/)