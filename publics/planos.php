<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos - NetoNerd</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        /* body {
            background-color: #111;
            color: #fff;
            font-family: 'Arial', sans-serif;
        }
        .hero {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(135deg, #007bff, #00c3ff);
            color: white;
        }
        .plan-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        .plan-card {
            background: #222;
            border-radius: 15px;
            padding: 30px;
            margin: 20px;
            width: 350px;
            box-shadow: 0 0 15px rgba(0, 123, 255, 0.5);
            text-align: center;
            transition: transform 0.3s;
        }
        .plan-card:hover {
            transform: scale(1.05);
        }
        .plan-title {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #00c3ff;
        }
        .plan-price {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #00c3ff;
        }
        .plan-benefits {
            list-style: none;
            padding: 0;
        }
        .plan-benefits li {
            margin: 10px 0;
            font-size: 1.1rem;
        }
        .plan-btn {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 8px;
            border: none;
            background: #00c3ff;
            color: #fff;
            transition: background 0.3s;
        }
        .plan-btn:hover {
            background: #007bff;
        }
        .nav_planos{
          text-align: center;
          padding: 80px 20px;
          background: linear-gradient(135deg, #007bff, #00c3ff);
          color: white;
        } */
    </style>
    
</head>
<body>
  
  <!-- Navbar -->
  
    <div class="hero">
    <?php 
      include_once('../routes/header.php')
    ?>
        <h1>Planos NetoNerd</h1>
        <p>Escolha a melhor opção para garantir a máxima performance e segurança para seus computadores!</p>
    </div>

    <div class="container plan-container">
      <div class="plan-card">
        <h3 class="plan-title">Plano Inicial</h3>
        <p class="plan-price">R$ 400/mês</p>
        <ul class="plan-benefits">
          <li>✔ 1 visita presencial por mês</li>
          <li>✔ Suporte a Windows e Linux</li>
          <li>✔ Diagnóstico e otimização</li>
        </ul>
        <a href="https://wa.me/+5521977395867?text=Olá, gostaria de conhecer mais sobre o plano Inicial" class="plan-btn">Contratar</a>
      </div>

      <div class="plan-card">
        <h3 class="plan-title">Plano Intermediário</h3>
        <p class="plan-price">R$ 500/mês</p>
        <ul class="plan-benefits">
          <li>✔ 2 visitas por mês</li>
          <li>✔ Backup semestral gratuito</li>
          <li>✔ Diagnóstico preventivo</li>
          <li>✔ Treinamento para sua equipe</li>
          <li>✔ <strong>Limpeza interna inclusa!</strong></li>
        </ul>
        <a href="https://wa.me/+5521977395867?text=Olá, gostaria de conhecer mais sobre o plano Intermediário" class="plan-btn">Contratar</a>
      </div>

      <div class="plan-card">
        <h3 class="plan-title">Plano Avançado</h3>
        <p class="plan-price">R$ 600/mês</p>
        <ul class="plan-benefits">
          <li>✔ 4 visitas por mês</li>
          <li>✔ Backup trimestral gratuito</li>
          <li>✔ Suporte prioritário</li>
          <li>✔ Diagnóstico completo</li>
          <li>✔ <strong>Manutenção completa e limpeza interna!</strong></li>
        </ul>
        <a href="https://wa.me/+5521977395867?text=Olá, gostaria de conhecer mais sobre o plano Avançado" class="plan-btn">Contratar</a>
      </div>
    </div>

    <?php 
      include_once('../routes/footer.php')
    ?>
</body>
</html>
