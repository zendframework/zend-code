<?php

use ZendTest\Code\TestAsset\InternalHintsClass;
use ZendTest\Code\TestAsset\NullableReturnTypeHintedClass;
use ZendTest\Code\TestAsset\ReturnTypeHintedClass;

function voidReturn() : void
{
}

function arrayReturn() : array
{
}

function callableReturn() : callable
{
}

function intReturn() : int
{
}

function floatReturn() : float
{
}

function stringReturn() : string
{
}

function boolReturn() : bool
{
}

function classReturn() : ReturnTypeHintedClass
{
}

function otherClassReturn() : InternalHintsClass
{
}

function nullableArrayReturn() : ?array
{
}

function nullableCallableReturn() : ?callable
{
}

function nullableIntReturn() : ?int
{
}

function nullableFloatReturn() : ?float
{
}

function nullableStringReturn() : ?string
{
}

function nullableBoolReturn() : ?bool
{
}

function nullableClassReturn() : ?NullableReturnTypeHintedClass
{
}

function nullableOtherClassReturn() : ?InternalHintsClass
{
}

function iterableReturnValue() : iterable
{
}

function nullableIterableReturnValue() : ?iterable
{
}


function objectReturnValue() : object
{
}

function nullableObjectReturnValue() : ?object
{
}