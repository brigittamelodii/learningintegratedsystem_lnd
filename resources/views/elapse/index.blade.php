@extends('layouts.app')

@section('content')
    <style>
        body {
            background-color: #f4f4f4;
        }

        .container {
            margin-top: 40px;
            display: flex;
            justify-content: center;
            gap: 24px;
            flex-wrap: wrap;
        }

        .card {
            width: 180px;
            height: 400px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            text-align: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .blue {
            background-color: #085de8;
        }

        .gray {
            background-color: #999999;
        }

        .pink {
            background-color: #ff00ff;
        }

        .glow-blue {
            box-shadow: 0 0 20px 5px rgba(8, 93, 232, 0.6);
        }

        .glow-gray {
            box-shadow: 0 0 20px 5px rgba(153, 153, 153, 0.6);
        }

        .glow-pink {
            box-shadow: 0 0 20px 5px rgba(255, 0, 255, 0.6);
        }
    </style>

    <div class="container">
        <div class="card blue" data-title="Commitment Letter" data-glow="glow-blue">
            Commitment<br>Letter
        </div>
        <div class="card gray" data-title="E-Statement Letter for Consumer Loan Take Over" data-glow="glow-gray">
            E-Statement<br>Letter for<br>Consumer Loan<br>Take Over
        </div>
        <div class="card gray" data-title="E-Statement Letter for Frontliner Services" data-glow="glow-gray">
            E-Statement<br>Letter for<br>Frontliner<br>Services
        </div>
        <div class="card pink" data-title="Employee Consent (Personal Data Protection Privacy)" data-glow="glow-pink">
            Employee<br>Consent<br>(Personal Data<br>Protection<br>Privacy)
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');

            cards.forEach(card => {
                const glowClass = card.getAttribute('data-glow');

                card.addEventListener('mouseenter', function() {
                    this.classList.add(glowClass);
                });

                card.addEventListener('mouseleave', function() {
                    this.classList.remove(glowClass);
                });
            });
        });
    </script>
@endsection
