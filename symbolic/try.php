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



final class changeCodeStructureNodes extends NodeVisitorAbstract
{

    public function leaveNode(Node $node)
    {
        $array_Global_variables = array('_GET','_POST', '_COOKIE', '_SESION', '_FILE', '_REQUEST');

        $obj = new Path_Evaluation();

        if ($node instanceof Variable
        && !(in_array($node->name, $array_Global_variables) ))
        {
            $environments[$node->name] = '_Unknow_Argument_:symbol_String' ;
            $obj->add_node($node->name, '_Unknow_Argument_:symbol_String');
        }
        else
        if ($node instanceof ArrayDimFetch
            && $node->var instanceof Variable
            && (in_array($node->var->name, $array_Global_variables) ))
        {
            $variableName = (string) $node->var->name;
            $node->var->name = $variableName.'_'.$node->dim->value.'_symbol';
            $environments[$node->dim->value] = $node->var->name .':symbol_SuperGlobal';
            $obj->add_node($node->dim->value, $node->var->name .':symbol_SuperGlobal');

                return $node;
        }

    }
}


class Path_Evaluation{
    public static $nodes = array();

    public function add_node($key, $value)
    {
        Path_Evaluation::$nodes[$key] = $value;
    }

    public function eval_node($node){
        $node_class = get_class($node);

        switch ($node_class){
            case "PhpParser\Node\Expr\Variable":
                echo $node->name;
                return;
            case "PhpParser\Node\Expr\Assign":
                echo " <br><br> Assignment ";
                return;
            case "PhpParser\Node\Stmt\Expression":
                echo " <br><br> Expression ";
                return;
            case "PhpParser\Node\Stmt\Echo_":
                echo " <br><br> Echo ";
                return;
            case "PhpParser\Node\Expr\Print_":
                echo " <br><br> Print  ";
                return;
            case "PhpParser\Node\Stmt\Do_":
                echo " <br><br> Do ";
                return;
            case "PhpParser\Node\Stmt\For_":
                echo " <br><br> For  ";
                return;
            case "PhpParser\Node\Stmt\Foreach_":
                echo " <br><br> Foreach ";
                return;
            case "PhpParser\Node\Stmt\If_":
                echo " <br><br> IF Condition ";
                return;
            case "PhpParser\Node\Stmt\ElseIf_":
                echo " <br><br> Else IF ";
                return;
            case "PhpParser\Node\Stmt\Else_":
                echo " <br><br> Else ";
                return;
            case "PhpParser\Node\Stmt\Function_":
                echo " <br><br> Function ";
                return;
            case "PhpParser\Node\Expr\FuncCall":
                echo " <br><br> Function Call ";
                return;
            case "PhpParser\Node\Stmt\Return_":
                echo " <br><br> Return ";
                return;
            case "PhpParser\Node\Arg":
                echo " <br><br> Arg ";
                return;
            case "PhpParser\Node\Stmt\Switch_":
                echo " <br><br> Switch ";
                return;
            case "PhpParser\Node\Stmt\Break_":
                echo " <br><br> Break ";
                return;
            case "PhpParser\Node\Stmt\While_":
                echo " <br><br> While ";
                return;
            case "PhpParser\Node\Expr\ArrayDimFetch":
                echo " <br><br> Array Dim ";
                return;
            case "PhpParser\Node\Expr\ConstFetch":
                echo " <br><br> Constant ";
                break;
            case "PhpParser\Node\Scalar\Encapsed":
                echo " <br><br> Excapsed  ";
                return;
            case "PhpParser\Node\Scalar\String_":
                echo " <br><br> String ";
                return;
            case "PhpParser\Node\Stmt\InlineHTML":
                echo " <br><br> Inline HTML  ";
                return;
        }
    }


    public function print_Environment()
    {
        foreach(Path_Evaluation::$nodes as $k => $v)
        {
            echo $k . " => (" . $v . " )<br>";
        }
    }

    public function print_Constraints()
    {

    }
}


// Start by get the file content
$filename = __DIR__ . '/../symbolic/code.php';
$fileContents = file_get_contents($filename);

// Print Source Code Example of code.php page
echo "Example: <br>";
echo "--------------------- <br>";
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

echo "<br> <br> Abstract Syntax Tree: <br>";
echo "--------------------- <br>";
echo $dumper->dump($nodes) . "<br><br>";


$obj2 = new Path_Evaluation();

echo "<br><br><br> Conditions: <br>";
$obj2->print_Constraints();
echo "--------------------- <br>";
echo "Environments: <br>";
$obj2->print_Environment();
echo "--------------------- <br>";
