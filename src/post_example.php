<?php

use PhpParser\PrettyPrinter;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

require __DIR__ . '/../vendor/autoload.php';

$filename = __DIR__ . '/../examples/some_code_with_post.php';
$fileContents = file_get_contents($filename);

// 1. parsing the file
$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
$nodes = $parser->parse($fileContents);


// 2. prepare traverse that will change the code
$traverser = new NodeTraverser();

final class ChangePostToSomethingElseNodeVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node)
    {
        if (! $node instanceof Variable) {
            return null;
        }

        $variableName = (string) $node->name;
        if (! in_array($variableName, ['_POST'], true)) {
            return null;
        }

        // change the name
        $node->name = 'This_should_not_be_used';
        return $node;
    }
}

$traverser->addVisitor(new ChangePostToSomethingElseNodeVisitor());


// 3. traverse nodes
$changedNodes = $traverser->traverse($nodes);


// 4. print it to file
$prettyPrinter = new PrettyPrinter\Standard;
$filenameChanged = $filename . '_changed.php';
$changedFileContent = $prettyPrinter->prettyPrintFile($nodes);
file_put_contents($filenameChanged, $changedFileContent);
