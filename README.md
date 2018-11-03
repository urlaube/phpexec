# PhpExec plugin
The PhpExec plugin is a dynamic content plugin for [Urlaub.be](https://github.com/urlaube/urlaube) that allows an author to execute PHP code within content.

## Installation
Place the folder containing the plugin into your plugins directory located at `./user/plugins/`.

## Configuration
At the moment this plugin has no configuration.

## Usage
To execute PHP code on a page you may either use the shortcode `[php <filename>]` or `[php:raw <filename>]` where `<filename>` is the absolute path to a ".php" file. The `[php <filename>]` shortcode executes the PHP sourcecode, escapes HTML special characters and puts the output in the content. The `[php:raw <filename>]` shortcode executes the PHP sourcecode and puts the output in the content **without** escaping HTML special characters.
