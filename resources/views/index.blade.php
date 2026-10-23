<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Importadora PEBIE</title>

    <link rel="stylesheet" href="{{asset('css/bootstrap.min.css')}}">
    <script src="{{asset('js/bootstrap.min.js')}}"></script>

    <link rel="stylesheet" href="{{asset('datatables/datatables.min.css')}}">
    <script src="{{asset('datatables/datatables.min.js')}}"></script>

    <style>

    body{
        margin:0;
        padding:0;
        min-height:100vh;
        background:linear-gradient(135deg,#eef2f7,#d9e4f5);
        font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .contenedor-principal{
        min-height:100vh;
        display:flex;
        align-items:center;
    }

    /* ==========================
       FORMULARIO
    ========================== */

    #formulario{
        width:100%;
        max-width:500px;
        background:white;
        padding:40px;
        border-radius:25px;
        box-shadow:0 15px 35px rgba(0,0,0,0.15);
        position:relative;
        overflow:hidden;
    }

    #formulario::before{
        content:"";
        position:absolute;
        top:50%;
        left:50%;
        transform:translate(-50%,-50%);
        width:280px;
        height:280px;

        background:url('{{asset("img/logo.jpeg")}}') no-repeat center center;
        background-size:contain;

        opacity:0.35;
        z-index:0;
    }

    #formulario *{
        position:relative;
        z-index:1;
    }

    .empresa{
        text-align:center;
        margin-bottom:10px;
    }

    .empresa h2{
        color:#0d3b66;
        font-weight:bold;
        margin-bottom:5px;
    }

    .empresa p{
        color:#6c757d;
        margin:0;
        font-size:14px;
    }

    h3{
        text-align:center;
        color:#0d3b66;
        font-weight:700;
        margin-bottom:30px;
        text-transform:uppercase;
        font-size:22px;
    }

    .form-group label{
        font-weight:600;
        color:#343a40;
    }

    .form-control{
        border-radius:12px;
        padding:12px;
        border:1px solid #ced4da;
        background:rgba(255,255,255,0.45);
    }

    .form-control:focus{
        border-color:#0d3b66;
        box-shadow:0 0 0 0.2rem rgba(13,59,102,0.20);
    }

    .btn-ingresar{
        width:100%;
        background:#0d3b66;
        color:white;
        border:none;
        padding:12px;
        border-radius:50px;
        font-weight:bold;
        transition:.3s;
    }

    .btn-ingresar:hover{
        background:#082844;
        color:white;
        transform:translateY(-2px);
    }

   /* ==========================
   TRIÁNGULOS TIPO VS CODE PREMIUM
========================== */

.contenedor-productos{
    height:600px;
    position:relative;
}

/* RESPLANDOR DE FONDO */

.contenedor-productos::before{
    content:'';
    position:absolute;

    width:550px;
    height:550px;

    top:30px;
    left:20px;

    background:radial-gradient(
        circle,
        rgba(13,59,102,.12),
        transparent 70%
    );

    z-index:0;
}

/* TRIÁNGULO SUPERIOR */

.triangulo-superior{
    position:absolute;
    top:20px;
    left:80px;

    width:450px;
    height:320px;

    overflow:hidden;

    clip-path: polygon(
        0 0,
        100% 50%,
        0 100%
    );

    border:2px solid rgba(255,255,255,0.9);

    /* SOMBRAS REALES PARA CLIP-PATH */
    filter:
        drop-shadow(0 25px 40px rgba(0,0,0,.45))
        drop-shadow(0 10px 25px rgba(13,59,102,.25));

    transition:all .4s ease;

    z-index:2;
}

.triangulo-superior:hover{
    transform:translateY(-10px) scale(1.03);

    filter:
        drop-shadow(0 35px 55px rgba(0,0,0,.55))
        drop-shadow(0 0 25px rgba(13,59,102,.35));

    z-index:20;
}

.triangulo-superior img{
    width:140%;
    height:140%;
    object-fit:cover;

    margin-left:-120px;
    margin-top:-60px;

    transition:.5s;
}

.triangulo-superior:hover img{
    transform:scale(1.08);
}


/* TRIÁNGULO INFERIOR */

.triangulo-inferior{
    position:absolute;
    top:250px;
    left:80px;

    width:450px;
    height:320px;

    overflow:hidden;

    clip-path: polygon(
        100% 0,
        100% 100%,
        0 50%
    );

    border:2px solid rgba(255,255,255,0.9);

    /* SOMBRAS REALES PARA CLIP-PATH */
    filter:
        drop-shadow(0 25px 40px rgba(0,0,0,.45))
        drop-shadow(0 10px 25px rgba(13,59,102,.25));

    transition:all .4s ease;

    z-index:1;
}

.triangulo-inferior:hover{
    transform:translateY(-10px) scale(1.03);

    filter:
        drop-shadow(0 35px 55px rgba(0,0,0,.55))
        drop-shadow(0 0 25px rgba(13,59,102,.35));

    z-index:20;
}

.triangulo-inferior img{
    width:140%;
    height:140%;
    object-fit:cover;

    margin-left:-40px;
    margin-top:-60px;

    transition:.5s;
}

.triangulo-inferior:hover img{
    transform:scale(1.08);
}
</style>
<div class="row w-100 align-items-center">

    <!-- FORMULARIO -->
    <div class="col-lg-6 d-flex justify-content-center">

        <div id="formulario">

            <div class="empresa">
                <h2>IMPORTADORA PEBIE</h2>
                <p>Enseres y artículos para el hogar</p>
            </div>

            <h3>Ingreso al Sistema</h3>

            @if(session('error_login'))
            <div class="alert alert-danger py-2 small mb-3 border-0 shadow-sm text-center" role="alert">
                <i class="bi bi-exclamation-circle me-1"></i> {{ session('error_login') }}
            </div>
            @endif

            @if(session('mensaje'))
            <div class="alert alert-success py-2 small mb-3 border-0 shadow-sm text-center" role="alert">
                <i class="bi bi-check-circle me-1"></i> {{ session('mensaje') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger py-2 small mb-3 border-0 shadow-sm" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="post" action="{{ route('validar') }}">
                @csrf

                <div class="form-group">
                    <label>Usuario</label>
                    <input
                        type="text"
                        name="usu"
                        required
                        class="form-control"
                        placeholder="Ingrese su usuario">
                </div>

                <div class="form-group mt-3">
                    <label>Contraseña</label>
                    <input
                        type="password"
                        name="pwd"
                        required
                        class="form-control"
                        placeholder="Ingrese su contraseña">
                </div>

                <div class="mt-4">
                    <input
                        type="submit"
                        value="Ingresar"
                        class="btn btn-ingresar">
                </div>

            </form>

        </div>

    </div>

    <div class="col-lg-6">

    <div class="contenedor-productos">

        <div class="triangulo-superior">
            <img id="imgSuperior" src="{{asset('img/productos/1.jpg')}}" alt="Producto 1">
        </div>

        <div class="triangulo-inferior">
            <img id="imgInferior" src="{{asset('img/productos/18.jpg')}}" alt="Producto 2">
        </div>

    </div>

</div>

</div>
<script>

let indiceSuperior = 1;
let indiceInferior = 18;

const imgSuperior = document.getElementById('imgSuperior');
const imgInferior = document.getElementById('imgInferior');

setInterval(() => {

    indiceSuperior++;

    if(indiceSuperior > 18){
        indiceSuperior = 1;
    }

    indiceInferior--;

    if(indiceInferior < 1){
        indiceInferior = 18;
    }

    imgSuperior.src = "{{asset('img/productos')}}/" + indiceSuperior + ".jpg";

    imgInferior.src = "{{asset('img/productos')}}/" + indiceInferior + ".jpg";

}, 3000);

</script>

</body>
</html>