<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - {{ config('app.name') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('liceo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('liceo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('liceo.png') }}">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Varela+Round" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
    <style>
        body {
            background-color: #1C2127;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        
        .container {
            position: absolute;
            right: 30px;
        }
        
        .message {
            font-family: 'Poppins', sans-serif;
            font-size: 30px;
            color: white;
            font-weight: 500;
            position: absolute;
            top: 230px;
            left: 40px;
        }
        
        .message2 {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            color: white;
            font-weight: 300;
            width: 360px;
            position: absolute;
            top: 280px;
            left: 40px;
        }

        .action-buttons {
            font-family: 'Poppins', sans-serif;
            position: absolute;
            top: 350px;
            left: 40px;
            width: 360px;
        }

        .btn {
            display: inline-block;
            margin: 10px 10px 0 0;
            padding: 12px 20px;
            background-color: #FF4757;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 0 10px rgba(255, 71, 87, 0.3);
        }

        .btn:hover {
            background-color: #FF3742;
            box-shadow: 0 0 20px rgba(255, 71, 87, 0.6);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: transparent;
            color: #FF4757;
            border: 2px solid #FF4757;
        }

        .btn-secondary:hover {
            background-color: #FF4757;
            color: white;
        }
        
        .neon {
            text-align: center;
            width: 300px;
            margin-top: 30px;
            margin-bottom: 10px;
            font-family: 'Varela Round', sans-serif;
            font-size: 90px;
            color: #FF4757;
            letter-spacing: 3px;
            text-shadow: 0 0 5px #FF6B7D;
            animation: flux 2s linear infinite;
        }
        
        .door-frame {
            height: 495px;
            width: 295px;
            border-radius: 90px 90px 0 0;
            background-color: #8594A5;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .door {
            height: 450px;
            width: 250px;
            border-radius: 70px 70px 0 0;
            background-color: #A0AEC0;
        }

        .eye {
            top: 15px;
            left: 25px;
            height: 5px;
            width: 15px;
            border-radius: 50%;
            background-color: white;
            animation: eye 7s ease-in-out infinite;
            position: absolute;
        }
        
        .eye2 {
            left: 65px;
        }

        .window {
            height: 40px;
            width: 130px;
            background-color: #1C2127;
            border-radius: 3px;
            margin: 80px auto;
            position: relative;
        }

        .leaf {
            height: 40px;
            width: 130px;
            background-color: #8594A5;
            border-radius: 3px;
            margin: 80px auto;
            animation: leaf 7s infinite;
            transform-origin: right;
        }

        .handle {
            height: 8px;
            width: 50px;
            border-radius: 4px;
            background-color: #EBF3FC;
            position: absolute;
            margin-top: 250px;
            margin-left: 30px;
        }

        .rectangle {
            height: 70px;
            width: 25px;
            background-color: #CBD8E6;
            border-radius: 4px;
            position: absolute;
            margin-top: 220px;
            margin-left: 20px;
        }

        .footer {
            position: absolute;
            bottom: 20px;
            left: 40px;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            color: #FF6B7D;
        }

        @keyframes leaf {
            0% {
                transform: scaleX(1);
            }
            5% {
                transform: scaleX(0.2);
            } 
            70% {
                transform: scaleX(0.2);
            }
            75% {
                transform: scaleX(1);
            }
            100% {
                transform: scaleX(1);
            }
        }

        @keyframes eye {
            0% {
                opacity: 0;
                transform: translateX(0)
            }
            5% {
                opacity: 0;
            }
            15% {
                opacity: 1;
                transform: translateX(0)
            }
            20% {
                transform: translateX(15px)
            }
            35% {
                transform: translateX(15px)
            }
            40% {
                transform: translateX(-15px)
            }
            60% {
                transform: translateX(-15px)
            }
            65% {
                transform: translateX(0)
            }
        }

        @keyframes flux {
            0%,
            100% {
                text-shadow: 0 0 5px #FF6B7D, 0 0 15px #FF6B7D, 0 0 50px #FF6B7D, 0 0 50px #FF6B7D, 0 0 2px #FFB3BA, 2px 2px 3px #FF4757;
                color: #FF3742;
            }
            50% {
                text-shadow: 0 0 3px #CC1E2D, 0 0 7px #CC1E2D, 0 0 25px #CC1E2D, 0 0 25px #CC1E2D, 0 0 2px #CC1E2D, 2px 2px 3px #8B0000;
                color: #FF6B7D;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                position: relative;
                right: auto;
                margin: 20px auto;
                width: 295px;
            }
            
            .message, .message2, .action-buttons {
                position: relative;
                left: 0;
                top: auto;
                margin: 20px 0;
                width: 100%;
            }
            
            .message {
                font-size: 24px;
                text-align: center;
            }
            
            .message2 {
                font-size: 16px;
                text-align: center;
            }
            
            .action-buttons {
                text-align: center;
            }
            
            .btn {
                display: block;
                margin: 10px auto;
                width: 200px;
            }
            
            .footer {
                position: relative;
                left: 0;
                bottom: auto;
                margin: 20px 0;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="message">No tienes autorización.</div>
    <div class="message2">Intentaste acceder a una página para la cual no tienes permisos previos en {{ config('app.name') }}.</div>
    
    <div class="action-buttons">
        @auth
            <a href="{{ url('/liceo') }}" class="btn">Ir al Panel Principal</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Volver Atrás</a>
        @else
            <a href="{{ url('/liceo/login') }}" class="btn">Iniciar Sesión</a>
            <a href="{{ url('/') }}" class="btn btn-secondary">Ir al Inicio</a>
        @endauth
    </div>
    
    <div class="container">
        <div class="neon">403</div>
        <div class="door-frame">
            <div class="door">
                <div class="rectangle"></div>
                <div class="handle"></div>
                <div class="window">
                    <div class="eye"></div>
                    <div class="eye eye2"></div>
                    <div class="leaf"></div> 
                </div>
            </div>  
        </div>
    </div>

    <div class="footer">
        © {{ date('Y') }} {{ config('app.name') }} - Liceo del Saber
    </div>

    <script>
        @auth
        let countdown = 30;
        const interval = setInterval(function() {
            countdown--;
            if (countdown <= 0) {
                window.location.href = '{{ url("/liceo") }}';
                clearInterval(interval);
            }
        }, 1000);
        @endauth
    </script>
</body>
</html>
