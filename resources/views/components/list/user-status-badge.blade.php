@props(['status'])

<span @class(['badge','text-bg-success'=> $status===1,'text-bg-warning'=>$status===0,'text-bg-danger'=>$status===2,'text-bg-info'=>$status===3])>

    @switch($status)
        @case(1)
            Activo
        @break
        @case(2)
             Inactivo
        @break
        @case(3)
             Por Autorizar
        @break
        @default
            Eliminado

    @endswitch

</span>
