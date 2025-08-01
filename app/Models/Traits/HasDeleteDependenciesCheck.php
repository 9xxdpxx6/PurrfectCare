<?php

namespace App\Models\Traits;

trait HasDeleteDependenciesCheck
{
    /**
     * Проверяет наличие зависимых записей по указанным связям.
     * Возвращает текст ошибки (или null, если зависимостей нет)
     */
    public function hasDependencies(): ?string
    {
        foreach (($this->deleteDependencies ?? []) as $relation => $message) {
            if (method_exists($this, $relation) && $this->$relation()->exists()) {
                return $message;
            }
        }
        return null;
    }
}