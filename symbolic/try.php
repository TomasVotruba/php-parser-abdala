<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpParser\NodeDumper;
use PhpParser\ParserFactory;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\Variable;
use PhpParser\PrettyPrinter;


$filename = __DIR__ . '/../symbolic/code.php';
$fileContents = file_get_contents($filename);

$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
$nodes = $parser->parse($fileContents);

$traverser = new NodeTraverser();


final class changeCodeStructureNodes extends NodeVisitorAbstract
{


    public function leaveNode(Node $node)
    {
//        if ($node instanceof Node\Expr\ArrayDimFetch
//            && $node->var instanceof Node\Expr\Variable
//            && $node->var->name === '_POST'
//        ) {
//            echo $node->dim->value;
//        }
        $array_Global_variables = array('_GET','_POST', '_COOKIE', '_SESION', '_FILE', '_REQUEST');

        if ($node instanceof ArrayDimFetch
            && $node->var instanceof Variable
            && (in_array($node->var->name, $array_Global_variables) ))
        {

            $variableName = (string) $node->var->name;
            $node->var->name = $variableName.'_'.$node->dim->value.'_symbol';
            return $node;
        }

//        if ($node instanceof Variable  && empty($node->value))
//        {
//
//            $node->value = '_Unknow_Argument_';
//            return $node;
//        }
    }
}
$traverser->addVisitor(new changeCodeStructureNodes());


// 3. traverse nodes
$changedNodes = $traverser->traverse($nodes);


// 4. print it to file
//$prettyPrinter = new PrettyPrinter\Standard;
//$filenameChanged = $filename . '_changed.php';
//$changedFileContent = $prettyPrinter->prettyPrintFile($nodes);
//file_put_contents($filenameChanged, $changedFileContent);

$dumper = new NodeDumper;
echo $dumper->dump($nodes) . "\n";




