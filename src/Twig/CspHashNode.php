<?php declare(strict_types=1);


namespace GravitateNZ\fta\csp\Twig;


use Twig\Compiler;
use Twig\Node\Node;

class CspHashNode extends Node
{
    public function compile(Compiler $compiler)
    {
        $body = $this->getNode('body');
        $output = <<< EOD
\$s = ob_get_clean(); 
\$this->env->getExtension(GravitateNZ\\fta\\csp\\Twig\\CspExtension::class)->addCspHash(\$s);
echo \$s;

EOD;

        $compiler
            ->addDebugInfo($this)
            ->write("ob_start();\n")
            ->subcompile($body)
            ->write($output);
    }
}