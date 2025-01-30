<?php

namespace Lox;

class Scanner
{
    public array $tokens = [];
    private int $start = 0; // start of the lexeme
    private int $current = 0; // pointer of current char
    private int $line = 1;

    public function __construct(public string $source)
    {
        //
    }

    public function scanTokens(): array
    {
        while (!$this->isAtEnd()) {
            $this->start = $this->current;
            $this->scanToken();
        }
        array_push($this->tokens, new Token(TokenType::EOF, "", null, $this->line));
        return $this->tokens;
    }

    private function isAtEnd(): bool
    {
        return $this->current >= strlen($this->source);
    }

    private function scanToken()
    {
        $c = $this->advance();
        if ($c == null) return;

        switch ($c) {
            case '(':
                $this->addToken(TokenType::LEFT_PAREN);
                break;
            case ')':
                $this->addToken(TokenType::RIGHT_PAREN);
                break;
            case '{':
                $this->addToken(TokenType::LEFT_BRACE);
                break;
            case '}':
                $this->addToken(TokenType::RIGHT_BRACE);
                break;
            case ',':
                $this->addToken(TokenType::COMMA);
                break;
            case '.':
                $this->addToken(TokenType::DOT);
                break;
            case '-':
                $this->addToken(TokenType::MINUS);
                break;
            case '+':
                $this->addToken(TokenType::PLUS);
                break;
            case ';':
                $this->addToken(TokenType::SEMICOLON);
                break;
            case '*':
                $this->addToken(TokenType::STAR);
                break;
            case '!':
                $this->addToken($this->match('=') ? TokenType::BANG_EQUAL : TokenType::BANG);
                break;
            case '=':
                $this->addToken($this->match('=') ? TokenType::EQUAL_EQUAL : TokenType::EQUAL);
                break;
            case '<':
                $this->addToken($this->match('=') ? TokenType::LESS_EQUAL : TokenType::LESS);
                break;
            case '>':
                $this->addToken($this->match('=') ? TokenType::GREATER_EQUAL : TokenType::GREATER);
                break;
            case '/':
                if ($this->match('/')) {
                    while ((strcmp($this->peek(), PHP_EOL) !== 0) && !$this->isAtEnd()) {
                        $this->advance();
                    };
                } else {
                    $this->addToken(TokenType::SLASH);
                }
                break;
            case ' ':
            case '\r':
            case '\t':
                // ignore whitespace
                break;
            case PHP_EOL:
                $this->line++;
                break;
            case '"':
                $this->string();
                break;
            default:
                if (is_numeric($c)) {
                    $this->number();
                } else {
                    Lox::error($this->line, "Unexpected character {$c}.");
                }
                break;
        }
    }

    private function number(): void
    {
        while (is_numeric($this->peek())) $this->advance();

        // Look for a fractional part.
        if ($this->peek() == '.' && is_numeric($this->peekNext())) {
            // Consume the "."
            $this->advance();

            while (is_numeric($this->peek())) $this->advance();
        }

        $text = substr($this->source, $this->start, $this->current - $this->start);
        $this->addToken(TokenType::NUMBER, floatval($text));
    }

    private function string(): void
    {
        while (strcmp($this->peek(), '"') !== 0 && !$this->isAtEnd()) {
            if (strcmp($this->peek(), PHP_EOL) === 0) $this->line++;
            $this->advance();
        }

        if ($this->isAtEnd()) {
            Lox::error($this->line, "Unterminated String.");
            return;
        }

        // the closing "
        $this->advance();

        $len = $this->current - $this->start - 1; // -1 for the +1 in the offset
        $value = substr($this->source, $this->start + 1, $len - 1);
        $this->addToken(TokenType::STRING, $value);
    }

    private function match(string $expected): bool
    {
        if ($this->isAtEnd()) return false;
        if ($this->source[$this->current] != $expected) return false;

        $this->current++;
        return true;
    }

    private function peek(): string
    {
        if ($this->isAtEnd()) return '\0';

        return $this->source[$this->current] ?? '\0';
    }

    private function peekNext(): string
    {
        if ($this->current + 1 >= strlen($this->source)) return '\0';
        return $this->source[$this->current + 1] ?? '\0';
    }

    private function advance(): ?string
    {
        return $this->source[$this->current++] ?? null;
    }

    private function addToken(TokenType $type, mixed $literal = null)
    {
        // get the lexeme
        $text = substr($this->source, $this->start, $this->current - $this->start);
        array_push($this->tokens, new Token($type, $text, $literal, $this->line));
    }
}
