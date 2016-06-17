
//
// element-dataset 1.3.0
//
// element-dataset is released under the terms of the BSD-3-Clause license.
// (c) 2015 - 2016 Mark Milstein <mark@epiloque.com>
//
// For all details and documentation: https://github.com/epiloque/element-dataset
//

!function(e){if("object"==typeof exports&&"undefined"!=typeof module)module.exports=e();else if("function"==typeof define&&define.amd)define([],e);else{var t;t="undefined"!=typeof window?window:"undefined"!=typeof global?global:"undefined"!=typeof self?self:this,t.elementDataset=e()}}(function(){return function e(t,n,r){function o(u,f){if(!n[u]){if(!t[u]){var a="function"==typeof require&&require;if(!f&&a)return a(u,!0);if(i)return i(u,!0);var s=new Error("Cannot find module '"+u+"'");throw s.code="MODULE_NOT_FOUND",s}var d=n[u]={exports:{}};t[u][0].call(d.exports,function(e){var n=t[u][1][e];return o(n?n:e)},d,d.exports,e,t,n,r)}return n[u].exports}for(var i="function"==typeof require&&require,u=0;u<r.length;u++)o(r[u]);return o}({1:[function(e,t,n){"use strict";function r(){if(!(document.documentElement.dataset||Object.getOwnPropertyDescriptor(Element.prototype,"dataset")&&Object.getOwnPropertyDescriptor(Element.prototype,"dataset").get)){var e={};e.enumerable=!0,e.get=function(){function e(e){return e.charAt(1).toUpperCase()}function t(){return this.value}function n(e,t){"undefined"!=typeof t?this.setAttribute(e,t):this.removeAttribute(e)}for(var r=this,o={},i=this.attributes,u=0;u<i.length;u++){var f=i[u];if(f&&f.name&&/^data-\w[\w\-]*$/.test(f.name)){var a=f.name,s=f.value,d=a.substr(5).replace(/-./g,e);Object.defineProperty(o,d,{enumerable:this.enumerable,get:t.bind({value:s||""}),set:n.bind(r,a)})}}return o},Object.defineProperty(Element.prototype,"dataset",e)}}t.exports=r},{}]},{},[1])(1)});
