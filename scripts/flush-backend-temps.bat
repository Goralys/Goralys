@echo off

rmdir /s /q backend\Logs
rmdir /s /q backend\RateLimiter

mkdir backend\RateLimiter
mkdir backend\Logs