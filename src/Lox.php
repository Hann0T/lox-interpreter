<?php

namespace Lox;

require "vendor/autoload.php";

class Lox
{
    static bool $had_error = false;

    static function main(array $args)
    {
        if (count($args) > 2) {
            printf("Usage: lox [script]");
            exit(64);
        } elseif (count($args) == 2) {
            Lox::run_file($args[1]);
        } else {
            Lox::run_prompt();
        }

        exit(0);
    }

    static function run_file(string $path)
    {
        $file_content = file_get_contents($path);
        Lox::run($file_content);

        if (Lox::$had_error) exit(65);
    }

    static function run_prompt()
    {
        $f = fopen('php://stdin', 'r');

        printf("> ");
        while ($line = fgets($f)) {
            Lox::run($line);
            Lox::$had_error = false;

            printf("> ");
        }

        fclose($f);
    }

    static function run(string $source)
    {
        $scanner = new Scanner($source);
        $tokens = $scanner->scanTokens();
        foreach ($tokens as $token) {
            printf("TOKEN: %s\n", $token);
        }
    }

    static function error(int $line, string $message)
    {
        // Lox::report($line, "", $message);
    }

    static function report(int $line, string $where, string $message)
    {
        printf("[line %s] Error %s: %s\n", $line, $where, $message);
        Lox::$had_error = true;
    }
}

Lox::main($argv);
