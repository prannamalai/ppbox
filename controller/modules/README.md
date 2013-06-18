Modules folder
=====================
This folder contains sub-directories specific to modules eg. checkin, checkout etc. Each of the modules should be registered in config/controller.ini for accepting requests through BaseController.

For module specific documentation navigate inside the module's folder.

Adding new modules
-----------------------------------
1. Create a new folder for your module inside this directory
2. Extend a new class from PayPalCallable to represent your module. Refer to existing modules for examples.
3. Inside your class, write methods to handle GET, POST, or any other http method. For example if you want to handle "get" requests, create a method with name get_<method name>. Again refer existing module classes for more clarity.
4. Write code inside this method to handle the request. To make use of existing packages, use "PackageFactory" class to load packages and get instances of individual package classes.
5. Add alias of the module to ppbox/controller/config/controller.ini file under "actions" section.
6. Add class path of the module class to ppbox/controller/config/controller.ini file under "class_paths" section.
7. Add alias for class method to ppbox/controller/config/controller.ini file under a new section for this module. (For example, see existing sections). The name of method here should not contain the http method prefix as the prefix will be added by framework. For example, if your method name is get_mymethod(), the entry in controller.ini file would be only "mymethod", the "get_" prefix will be added by framework when it receives GET requests for your method.

* Note: Module classes will act as orchestration layer only and should not perform any actual PayPal related fucntionality. All PayPal API interface functionality should be written inside packages, whose instances would be created in modules.
