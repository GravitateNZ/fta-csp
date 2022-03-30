<?php declare(strict_types=1);


namespace GravitateNZ\fta\csp\Twig;


use Twig\Error\SyntaxError;
use Twig\Node\Expression\TempNameExpression;
use Twig\Node\Node;
use Twig\Node\TextNode;
use Twig\Token;

class CspScriptTokenParser extends \Twig\TokenParser\AbstractTokenParser
{

    /**
     * @param CspExtension $extension
     */
    public function __construct(protected CspExtension $extension)
    {
    }


    /**
     * @inheritDoc
     */
    public function parse(Token $token): Node
    {
        $lineno = $token->getLine();
        $name = $this->parser->getVarName();

        $ref = new TempNameExpression($name, $lineno);
        $ref->setAttribute('always_defined', true);

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideApplyEnd'], true);
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new CspHashNode(
            ['body' => $body],
            [],
            $lineno,
            $this->getTag()
        );
    }

    /**
     * @inheritDoc
     */
    public function getTag(): string
    {
        return 'sha';
    }

    public function decideApplyEnd(Token $token): bool
    {
        return $token->test('endsha');
    }
}
