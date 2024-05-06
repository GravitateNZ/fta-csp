<?php declare(strict_types=1);


namespace GravitateNZ\fta\csp\Twig;


use Twig\Compiler;
use Twig\Node\Node;
use Twig\Node\TextNode;
use Twig\Node\CaptureNode;

class CspHashNode extends Node
{
    protected ?array $hash;

    public function __construct(
        Node $body, int $lineno, string $tag, ?array $hash = null,
    ) {
        $body = new CaptureNode($body, $lineno, $tag);
        $body->setAttribute('raw', true);
        $this->hash = $hash;
        parent::__construct(['body' => $body], [], $lineno, $tag);
    }


    public function compile(Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->indent()
            ->write("\$content = ")
            ->subcompile($this->getNode('body'))
            ->raw("\n")
            ->outdent();

        if ($this->hash) {
            $output = <<< EOD
\$this->env->getExtension(GravitateNZ\\fta\\csp\\Twig\\CspExtension::class)->addCspDirective("{$this->hash[0]}", "{$this->hash[1]}");
EOD;
        } else {
            $output = <<< EOD
\$this->env->getExtension(GravitateNZ\\fta\\csp\\Twig\\CspExtension::class)->addCspHash(\$content);
EOD;
        }

        $compiler->write($output);
        // or can we yield?
        $compiler->write("echo \$content;\n");
    }
}
