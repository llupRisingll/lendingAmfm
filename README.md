<h2 align="center"> <b>rutPHP</b> </h2>
<p align="center">
    A flexible/transformable lightweight php framework designed to maintain the creativity of a programmer while maintaining software structure's simplicity.
    <br>
    <a href="bin/docs/README.md"><b>Explore rutPHP docs »</b></a>
</p>

## Basic Routing
rutPHP framework routing provides an easy routing configuration. The page routing is written in JSON (Javascript Object Notation) and you don't have to declare a request method.

```json
{
    "[uri]": "[Presenter].php"
}
```


## What's included
Within the download you'll find the following directories and files, logically grouping common assets and providing both compiled and minified variations. You'll see something like this:
```
rutPHP/
├── app/
│   ├── layouts/
│   |   ├── [Presenter]/
│   |   |   ├── index.leaf
│   |   |   ├── index.css (Optional)
│   |   |   ├── index.js (Optional)
│   |   ├── defaults/
│   |   |   ├── 400.html (Do not rename)
│   |   |   ├── 401.html (Do not rename)
│   |   |   ├── 403.html (Do not rename)
│   |   |   ├── 404.html (Do not rename)
│   |   |   ├── 500.html (Do not rename)
│   |   ├── barebone.leaf (Do not touch)
│   |   ├── ...
│   ├── models/ (Optional)
│   ├── presenters/
│   |   ├── [Presenter].php
│   |   ├── ...
│   └── urls.json
├── bin/
│   ├── autoload.php
│   ├── ErrorView.php
│   ├── Generate.php
│   ├── Loader.php
│   ├── Params.php
│   ├── Route.php
│   ├── View.php
├── .htaccess
├── vendors/
└── rut.php
```
## Bugs and feature requests
Have a bug or a feature request? Please first read the [issue guidelines](#) and search for existing and closed issues. If your problem or idea is not addressed yet, [please open a new issue](#).

## TODO:
* Advance Routing RegEx
* Presenter/Controller Classses
* Load only the presenters, then the presenter name is equal to lowered case layout name
* Use Routing.json instead

## Creator
**Luis Edward Miranda**
* [https://twitter.com/luisedward777](https://twitter.com/luisedward777)
* [https://github.com/llupRisingll](https://github.com/llupRisingll)

## Open-source License
Warnining this software is under [NPOSL-3.0](License). In other words, this project is free and open for non-commercial use only.