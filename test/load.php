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

global $conditions;
$environments = array();

// load PathGenerator
include 'build_path.php';

final class changeCodeStructureNodes extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        $obj = new PathGenerator();
        $obj->eval($node);
        return $node;
    }

}


// Start by get the file content
$filename = __DIR__ . '/example.php';
$fileContents = file_get_contents($filename);

// Print Source Code Example of code.php page
echo "<h3><b>Example: </b></h3><br>";
foreach (glob($filename) as $filename) {
    echo nl2br(file_get_contents($filename));
    echo "<br></br>";
}

// Create Parser
$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

// Parse the file content
$nodes = $parser->parse($fileContents);

// Add Node Visitor to Traverser
$traverser = new NodeTraverser();
$traverser->addVisitor(new changeCodeStructureNodes());


// 3. traverse nodes
$changedNodes = $traverser->traverse($nodes);


// Traverse and convert to AST
$dumper = new NodeDumper;

echo "<br> <hr><h3><b>Abstract Syntax Tree:</b></h3> <br>";
echo $dumper->dump($nodes) . "<br><br><br> ";

echo "<hr><b><h3>Result:</h3></b> <br>";
$obj2 = new PathOutput();
echo $obj2->get(1, "None");