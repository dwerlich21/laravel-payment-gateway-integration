<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionException;

class EnumController
{
    /**
     * Carrega automaticamente todos os enums do diretório App\Enums
     *
     * @return array
     */
    private function loadAllEnums(): array
    {
        $enums = [];
        $enumPath = app_path('Enums');
        
        if (!File::exists($enumPath)) {
            return $enums;
        }

        $enumFiles = File::files($enumPath);
        
        foreach ($enumFiles as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $fullClassName = "App\\Enums\\{$className}";
            
            try {
                if (class_exists($fullClassName)) {
                    $reflection = new ReflectionClass($fullClassName);
                    
                    // Verifica se é um enum e tem o método options()
                    if ($reflection->isEnum() && $reflection->hasMethod('options')) {
                        $enumKey = $this->classNameToSnakeCase($className);
                        $enums[$enumKey] = $fullClassName::options();
                    }
                }
            } catch (ReflectionException $e) {
                // Ignora classes que não podem ser carregadas
                continue;
            }
        }
        
        return $enums;
    }

    /**
     * Converte nome da classe para snake_case
     *
     * @param string $className
     * @return string
     */
    private function classNameToSnakeCase(string $className): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }

    /**
     * Encontra a classe do enum baseado na chave
     *
     * @param string $enumKey
     * @return string|null
     */
    private function findEnumClass(string $enumKey): ?string
    {
        $enumPath = app_path('Enums');
        
        if (!File::exists($enumPath)) {
            return null;
        }

        $enumFiles = File::files($enumPath);
        
        foreach ($enumFiles as $file) {
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $fullClassName = "App\\Enums\\{$className}";
            $classKey = $this->classNameToSnakeCase($className);
            
            if ($classKey === $enumKey && class_exists($fullClassName)) {
                try {
                    $reflection = new ReflectionClass($fullClassName);
                    if ($reflection->isEnum() && $reflection->hasMethod('options')) {
                        return $fullClassName;
                    }
                } catch (ReflectionException $e) {
                    continue;
                }
            }
        }
        
        return null;
    }

    /**
     * Retorna todos os options de enums disponíveis
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $enums = $this->loadAllEnums();

            return response()->json([
                'success' => true,
                'data' => $enums,
                'message' => 'Enums recuperados com sucesso.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar enums',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna options de um enum específico
     *
     * @param Request $request
     * @param string $enum
     * @return JsonResponse
     */
    public function show(Request $request, string $enum): JsonResponse
    {
        try {
            $enumKey = str_replace('-', '_', $enum);
            $enumClass = $this->findEnumClass($enumKey);

            if (!$enumClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enum não encontrado'
                ], 404);
            }

            $options = $enumClass::options();

            return response()->json([
                'success' => true,
                'data' => $options,
                'message' => 'Enum recuperado com sucesso.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar enum',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
