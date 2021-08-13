<?php declare(strict_types=1);


namespace GravitateNZ\fta\csp\Twig;


use Twig\Compiler;
use Twig\Node\Node;
use Twig\Node\TextNode;

class CspHashNode extends Node
{
    public function compile(Compiler $compiler)
    {
        $body = $this->getNode('body');

        if ($body instanceof TextNode) {
            $hash = $compiler->getEnvironment()->getExtension(CspExtension::class)->hash($body->getAttribute('data'));
            $output = <<< EOD
\$this->env->getExtension(GravitateNZ\\fta\\csp\\Twig\\CspExtension::class)->addCspDirective("{$hash[0]}", "{$hash[1]}");
echo ob_get_clean();
EOD;
        } else {
            $output = <<< EOD
\$s = ob_get_clean(); 
\$this->env->getExtension(GravitateNZ\\fta\\csp\\Twig\\CspExtension::class)->addCspHash(\$s);
echo \$s;
EOD;
        }

        $compiler
            ->addDebugInfo($this)
            ->write("ob_start();\n")
            ->subcompile($body)
            ->write($output);
    }
}