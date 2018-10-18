## Basic CLI Usage

#### Generate Presenter and Layout Folder 
Use the following command to generate a presenter and dedicated layout directory for the application.
```
rut/rut create -p [Presenter Name]
```

A file in ``_presenters`` and ``layout`` folder will be generated.

##### Route, Presenter, Layout Process
When the file is generated and that file is routed in a URI. The process follows the following patterns:
1. The server receives a URI
2. Find the URI in the ``routing.json`` pass the URI query to the dedicated Presenter file.
3. Load the Presenter Algorithm
4. Display the Layout, if the Method of request is GET.


#### Generate Model Class
Use the following command to generate a model for the application.
```
rut/rut create -m [Model Name]
```
A file will be generated at ``_models`` folder. These model files can be used to manage different application models.

#### Route a URI to a Presenter-Layout Pair

Use the following command:
```
rut/rut route [URI] [PresenterFileName]
```
The command will automate the writing of URI at ``routing.json``. Routing dedicates a file into a requested URI.


#### Clear Cache

Use the following command:
```
rut/rut clean
```
This will remove all the saved caches. Very useful, when at development phase.


#### Compile/Uglify all of the JS and CSS in the layout folder

Use the following command:
```
rut/rut compile
```
Another useful at development phase. This will help to secure and minify the file for faster server response.

##### Preparing the assets/resources to be compiled
To prepare a list of files you wanted to compile, you have to create a file named ``assets.json`` within your layout folder. The output file will be named with the following formula:
````
md5([Layout Folder Name]) + ".min". "js/css"
````
**Example**: The output file for the ``Landing Layout`` Folder will be``41bd61e268fedccfb0d91dd571dd28b2.min.css``

The file generated will be saved inside the ``public`` folder.


## View/Layout Page Built-in Functions
The view layer uses twig as the templating engine, however we have sets of built-in functions that are useful when developing the entire application.

#### Form CSRF Token Protection
##### Form Helper
You can generate a form tag using a twig helper function ``form_tag()`` and ``end_form_tag()``. The ``end_form_tag`` is required to close the generated ``form_tag()``.
``form_tag()`` accepts the following parameters:
* action ``default: REQUIRED``
* method ``default: "GET"``
* id ``default: NULL``
* class ``default NULL``
* attr ``default NULL``

##### Form Token
You can generate a token to protect your server from an **unknown sources**. Using ``form_token()`` helper will help you accept the server request with the legitimate session pattern only. Also with this function you can lock a generated form token to an specific path using ``lock_to`` parameter.
* lock_to ``default NULL``

The token generation follows the following formula
```php
token = bin2hex( randombytes(32) )

if lock_to is not declared, then...
    token
else
    hash_hmac( algo='sha256', data=lock_to, key=token )

```

#### Default Assets
The ``default_assets()`` function handles and display the added Script and CSS declared within its presenter.