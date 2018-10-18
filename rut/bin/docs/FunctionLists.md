## View Class Static Public Function
#### addVar function
``addVar()`` allows you to add variable to the templating engine. The function required the following parameters:
* key ``default REQUIRED`` - this parameter will serve as the variable name.
* value ``default REQUIRED`` - this parameter will serve as the value of the variable declared.

#### getVars function
``gervars()`` returns the array of added Variable using ``addVar()``  function.

#### addFunction function
``addFunction()`` allows you to add a custom function to the templating engine. The function required the following parameter:
* function ``default REQUIRED`` - this parameter will serve as the Twig function. Remember, only Twig function is valid.

#### listFunction function
``listFunction()`` returns the array of added twig functions using ``addFunction()`` function.

#### formHelperChecker function
``formHelperChecker()`` will check your usage of form tag helper within the view layer. This will display an error message, and return a true or false value.

#### addScript function
``addScript()`` allows you to add ``<script/>`` tag within html's ``<head/>`` tag. This function required the following parameter:
* link ``default REQUIRED`` - this parameter is the link of the script to be added. 
* raw ``default False`` - this parameter is the raw option. The link parameter provided will be included to the script as an externally if the value is ``false`` and will be included internally if the provided value if ``true``

#### addCSS function
``addCSS()`` allows you to add either ``<link/>`` or ``<style/>`` tag within html's ``<head/>``  tag. This function requires the following parameters similar to the ``addScript()`` function:
* link ``default REQUIRED`` - this parameter is the link of the script to be added. 
* raw ``default False`` - this parameter is the raw option. The link parameter provided will be included to the script as an externally if the value is ``false`` and will be included internally if the provided value if ``true``.

#### defaultFunction function
``defaultFunction()`` allows you to place the added CSS and Script to the ``<head/>`` within the barebone of the application's view layer. Removing it there will not include the added scripts and css using ``addScript()`` and ``addCSS()`` function.

## Params Class Static Public Function
#### getAll function
``getAll()`` returns the URI Parameters, it uses the ``getURIParams`` from the Route Class 

#### require function
``require()`` allows you to pull a single key from the hash and raises an error if its not there. This function allows multiple parameters.

#### permit function
``permit()`` returns the keys which are allowed and mars the hash as safe for mass assignment. This function allows multiple parameters.

#### get function
``get()`` returns the value of a given ``key`` parameter. This function requires the following parameter.
* key ``default REQUIRED`` - this parameter will be the key to find with in the URI params

## Route Class Static Public Function
#### requestMethod Variable
This contains the server's request method.

#### domainName Variable
This contains the server's domain name.

#### matchURI Variable
This contains the request's matched URI

#### domain Function
``domain()`` function returns the ``domainName`` variable.

#### config Function
``config()`` function returns the configuration value from the config JSON file. This function requires the following parameter:
* key ``default REQUIRED`` - this parameter is the key you want to find within the config JSON file.

#### getURIParams Function
``getURIParams()`` function returns the URI Parameters of the request.

#### renderTwigView Function
``renderTwigView()`` render a twig layout. This function requires the following parameter:
* layoutName ``default REQUIRED`` - this paramater is the Presenter Name/ Layout Folder to be used.

#### returnCode Function
``returnCode`` allows you to return an http code as a response and display an error message template.

