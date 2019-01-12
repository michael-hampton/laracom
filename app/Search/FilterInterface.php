<?php
use Illuminate\Http\Request;
interface FilterInterface {
public static function apply(Request $filters);
}
