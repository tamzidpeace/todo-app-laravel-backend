<?php

namespace App\GraphQL\Mutations;

use App\Models\Todo;

class DeleteTodo
{
    /**
     * Delete a todo item
     *
     * @param  mixed  $root
     * @param  array  $args
     * @return bool
     */
    public function __invoke($root, array $args): bool
    {
        $todo = Todo::find($args['id']);
        
        if (!$todo) {
            return false;
        }
        
        return $todo->delete();
    }
}