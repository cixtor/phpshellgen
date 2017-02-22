# PHP-Shell Generator

Simple web terminal developed using `PHP` and the `jcubic/jquery.terminal` library. This tool was created looking for speed, simplicity, flexibility and anonymity, rather than using `r57` or `c99` I prefer to use an interface with only the essential tools and customizable.

This project has a build tool to allow the customization of the shell, at this moment you can customize the `username` and `password` used to log into the shell, the `filename` of the compiled file and the default shell interpreter used to execute the commands switching between valid PHP shell functions.

Also, in the generated shell you will have some methods to change the interpreter once you are logged in to be flexible when you are executing commands, and a way to dynamically change the current working directory using sessions instead of GET parameters.

![cixtor phpshellgen example](http://cixtor.com/uploads/phpshell-generator-4.png)

### Options

| Parameters        | Description                                            |
| ----------------- | ------------------------------------------------------ |
| `-h \ --help`     | Print this message with the list of available options. |
| `-i \ --input`    | Specify the shell template to compile.                 |
| `-o \ --output`   | Specify the filename for the compiled shell.           |
| `-s \ --shell`    | Specify the default PHP interpreter.                   |
| `-u \ --username` | Specify the username to log into the php-shell.        |
| `-p \ --password` | Specify the password to log into the php-shell.        |
| `-l \ --lint`     | Enable the PHP linter on the compiled shell.           |

### Methods

| Method                   | Description                                                  |
| ------------------------ | ------------------------------------------------------------ |
| `set_interpreter`        | Set the PHP shell interpreter: `set_interpreter(shell_exec)` |
| `get_interpreter`        | Get the current PHP shell interpreter.                       |
| `get_disabled_functions` | Get the list of functions disabled through a `php.ini` file. |
| `get_php_version`        | Get the version of the PHP interpreter in execution time.    |
| `logout`                 | Close the current shell session.                             |
| `status`                 | Display all the configuration variables.                     |
| `cd new/folder/path/`    | Change the current working directory.                        |

### Compilation

The process consists basically in replace some lines of the template with the customizable data provided by the tool like the password or the shell interpreter, adding dependencies to implement the web terminal interface, reducing the tabular characters and new lines to finally get a shell of more or less `130K`.

![compilation](http://cixtor.com/uploads/phpshell-generator-1.png)

### 404 Not Found

When the shell is loaded you will see a page saying that the file was not found in the server, and even if you send a `HEAD` request to the URL where the shell was uploaded, you will see the HTTP status `404 Not Found`. Hit the tabulator key to place the cursor in the first field of the form that you will use to authenticate yourself.

![not_found](http://cixtor.com/uploads/phpshell-generator-2.png)
