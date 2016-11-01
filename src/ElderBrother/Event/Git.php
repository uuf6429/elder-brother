<?php

namespace uuf6429\ElderBrother\Event;

class Git
{
    const APPLYPATCH_MSG = 'git:applypatch-msg';
    const PRE_APPLYPATCH = 'git:pre-applypatch';
    const POST_APPLYPATCH = 'git:post-applypatch';
    const PRE_COMMIT = 'git:pre-commit';
    const PREPARE_COMMIT_MSG = 'git:prepare-commit-msg';
    const COMMIT_MSG = 'git:commit-msg';
    const POST_COMMIT = 'git:post-commit';
    const PRE_REBASE = 'git:pre-rebase';
    const POST_CHECKOUT = 'git:post-checkout';
    const POST_MERGE = 'git:post-merge';
    const PRE_RECEIVE = 'git:pre-receive';
    const UPDATE = 'git:update';
    const POST_RECEIVE = 'git:post-receive';
    const POST_UPDATE = 'git:post-update';
    const PRE_AUTO_GC = 'git:pre-auto-gc';
    const POST_REWRITE = 'git:post-rewrite';
    const PRE_PUSH = 'git:pre-push';
    
    /**
     * Returns list of git hook filenames.
     * 
     * @return string[]
     */
    public static function getHookFiles()
    {
        static $hooks = null;
        
        if(!$hooks){
            $hooks = array_map(
                function ($eventName) {
                    return '.git/hooks/' . substr($eventName, 4);
                },
                (new \ReflectionClass(static::class))
                    ->getConstants()
            );
        }
        
        return $hooks;
    }
}
