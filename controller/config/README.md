Config folder
=====================
This folder contains configuration files for configuration at framework level.

1. controller.ini - Configuration file for BaseController
2. log4php_config.xml - Configuration file for log4php library

controller.ini - More details
================================
The controller.ini file is main configuration file for the framework. This can be used for configuring modules and packages. However, more specific configuration for packages will be done inside individual package folders themselves to foster reusability.

It contains below sections:
i18n
---------
This is the internationalization section. It contains a single key called "key", which specifies the internationalization key for returning back the error messages and any other information from the framework. 

A folder pertinent to the supported locale should be present in the `controller/i18n` folder. For example, if key is set to `en-us` (the default), the folder `controller/i18n/en-us` should be present. Next, the string constants are picked from `strings.php` file inside the `en-us` folder. You can have multiple folders for each of the different supported locales, but only one locale can be active at a time.

If you don't want to support languages other than US English, you can leave this settings as is.

actions
----------
Entries in this section are used to map the names of actions supported by the framework to their respective actual class names. For example, the action `checkout` is mapped to a class called `CheckOut`. These entries are case sensitive.

In order to add a new action, follow steps mentioned in Contribution guide for adding new modules.

To stop supporting a given action, you can simply comment out its entry in this file or even remove it completely from this file.

class_paths
--------------
This section defines the class paths for the classes defined for each class (for actions in actions section above). The paths are relative to `controller/modules` folder. A class path should contain the name of file in which that class is defined.

packages
-------------
In ppbox, the packages also have an alias associated with them to make them transparent from the modules which are using them. This section contains the mapping of package alias name to its real folder name in `controller/packages` folder.

In order to add a new package to the framework, please read the Contributor guide to adding new packages.

To stop supporting a given package, you can simply comment out its entry in this file or even remove it completely from this file.

package_classes
----------------
Similar to package names, package class name also have aliases associated with them to make them transparent from the modules that are using them. This section contains the mapping of package alias names to the actual class names of the package. Of course, this only defines the entry point class for that package and does not restrict on the use of additional classes by the package.

&lt;action&gt;
------------
Each of the actions defined in the `actions` section above can support multiple methods. For example, checkout action can support credit_card as well as email checkout. So for each action, there should be one entry with its own name, and this entry would contain the methods supported by that action.

It would contain the mapping of alias of function name to the actual function name. 

Each method itself can be supported for multiple HTTP method headers. For example, method credit_card can be supported for POST and PUT both HTTP methods, the HTTP method such as 'post' is prepended to the function name defined in the config file automatically. So for this example, if for action `checkout`, a method `credit_card` is defined in this section, and it is called with `POST` HTTP method in the REST call, then the actual method called would be `post_credit_card`. You don't need to define `post_credit_card` and `put_credit_card` separately in the config file.

There is a fall-back mechanism also built in the routing function of the framework, which would allow for a little flexibility for routing function calls. For example, the routing mechanism determines (based on the REST call) that the function to be called in `CheckOut` class is `post_credit_card`. But it does not find a function with this name. Then it would try to call function with the name `credit_card`. If again that function is also not found, then it will make a final attempt for call a function with name `_default`.

You are not required to understand this fall-back mechanism if you are not defining your own module.

