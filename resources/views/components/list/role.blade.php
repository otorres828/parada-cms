@props(['role'])

@switch($role)
    @case(2)
        Administrador
        @break
    @case(3)
       Operador
       @break
    @case(4)
       Consultor
        @break
    @default

@endswitch
