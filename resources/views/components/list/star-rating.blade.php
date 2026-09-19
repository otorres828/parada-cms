@props(['rating'])

<div class="fs-7 w-100px">

    @for ($i = 1; $i <= 5; $i++) 
    
    <i @class(
        [ 
            'bi bi-star-fill' , 
            'text-secondary' => $rating <= $i,
            'text-warning'=> $rating >= $i,
        ])></i>

    @endfor

</div>