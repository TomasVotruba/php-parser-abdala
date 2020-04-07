<?php

class Variable {
    public  $has_rhs = true;
    private $name = null;
    public  $value = null;

    public function __construct($name, $has_rhs=false) {
        $this->name = $name;
        $this->has_rhs = $has_rhs;
    }
    public function set_value($val) {
        $this->value = $val;
    }
    // Set to have the right hand side.
    public function set_has_rhs() {
        $this->has_rhs = true;
    }
    public function has_rhs() {
        return $this->has_rhs;
    }
}



class PathGenerator
{

    private static $variables = array();

    public function add_variable($name, $has_rhs=false) {
        $var = new Variable($name, $has_rhs);
        PathGenerator::$variables[$name] = $var;
        return $var;
    }


    public function is_super_global($name) : bool {
        if ( $name == "_POST" ||
            $name == "_GET" ||
            $name == "_COOKIE" ||
            $name == "_REQUEST" ||
            $name == "_SERVER" ||
            $name == "_SESSION" )
            return true;
        return false;
    }

    public function eval($node) {

        $node_type = strval($node->getType());
//        echo "node->type=".$node_type."<br>";
        $out = null;

        switch ($node_type) {
            case "Stmt_Expression":
                break;
            case "Expr_Assign":
                $out .= $this->eval_assign($node);
                break;
            case "Expr_Variable":
                break;
            case "Expr_ArrayDimFetch":
                break;
            case "Scalar_LNumber":
                break;
            case "Scalar_String":
                break;
            case "Stmt_If":
                break;
            case "Stmt_Function":
                break;
            case "Stmt_Nop":
                break;
        }
        if ($out) {
            $po = new PathOutput();
            $po->add_stmt($out);
        }
    }

    public function eval_assign($expr) {
        // LHS
        $out = $expr->var->name." => ";

        $lhs_var = $this->add_variable($expr->var->name);
        $lhs_name = $expr->var->name;

        // RHS
        $rhs_name = "null";
        $binop = false;

        if (property_exists($expr->expr, "var")) {
            $rhs_name = $expr->expr->var->name;
        }
        else if (property_exists($expr->expr, "left")) {
            // Binary op
            $binop = true;
            $out .= $this->eval_binaryop($expr);
        }
        else {
            print_r($expr);
            if (property_exists($expr->expr, 'value'))
                $rhs_name = $expr->expr->value;  // Scalar
            else if (property_exists($expr->expr, 'var'))
                $rhs_name = $expr->expr->var;  // Variable
            else
                $rhs_name = $expr->expr->name;  // Variable
        }
        $lhs_var->set_has_rhs();
        if (!is_int($rhs_name))
            $rhs_var = $this->add_variable($rhs_name);

        if ($this->is_super_global($rhs_name))
            $type = "symbol_SuperGlobal";
        else if (!$binop)
            $type = "symbol_String";
        else
            $type = "";
        $rhs_type = $expr->expr->getType();
        if ($rhs_type=="Expr_ArrayDimFetch")
            $out .="(".$rhs_name."_".$expr->expr->dim->value."_symbol:".$type.") ";

        else if ($rhs_type=="Scalar_LNumber") {
            $this->set_var_value($lhs_name, $expr->expr->value);
            $out .=strval($expr->expr->value).":int ";
        }
        else {
            if ($rhs_var->has_rhs)
                $out .="(".$rhs_name."_Argument_:".$type.") <br>";
            else if (!$binop) {
                $out .= "(_Unknow_Argument_:".$type.") <br>";
                $out .= $rhs_name." => (_Unknow_Argument_:symbol_String) <br>";
            }
        }
        return $out;
    }

}



class PathOutput
{

    public static $template = array(
        0 => "Path index: {pathindex}",
        1 => "Condition: {condition}",
        2 => "Environment:",
        3 => "{stmts}"
    );

    public static $template_str = "Path index: {0} <br> Condition: <br> Environment: <br> {2} <br>";

    public static $stmts_collection = array();


    public function __constructor($path_number)
    {
    }


    public function add_stmt($s)
    {
        array_push(PathOutput::$stmts_collection, $s);
    }


    public function get($path_index, $cond = "")
    {
        $s1 = str_replace("{0}", strval($path_index), PathOutput::$template_str);
        $s2 = str_replace("{1}", $cond, $s1);
        $stmts = join("<br>", PathOutput::$stmts_collection);
        $rs = str_replace("{2}", $stmts, $s2);
        return $rs;
    }

}