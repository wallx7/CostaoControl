<?php include 'services/session.php'; ?>
<?php require_once 'services/auth.php'; ?>
<?php
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        // Tentar registrar
        $result = register($name, $email, $password);
        
        if (isset($result['success']) && $result['success']) {
            $success = 'Conta criada com sucesso! Redirecionando para login...';
            // Redirecionar após 2 segundos
            header("refresh:2;url=auth-signin.php");
        } else {
            $error = $result['error'] ?? 'Erro desconhecido ao criar conta.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br" class="h-100">
<head>
     <?php
    $subTitle = "Criar Conta";
    include 'partials/title-meta.php'; ?>
       <?php include 'partials/head-css.php' ?>
</head>

<body class="h-100">
     <div class="d-flex flex-column h-100 p-3">
          <div class="d-flex flex-column flex-grow-1">
               <div class="row h-100 justify-content-center align-items-center">
                    <div class="col-xxl-7">
                         <div class="row justify-content-center h-100">
                              <div class="col-lg-6 py-lg-5 mx-auto">
                                   <div class="d-flex flex-column h-100 justify-content-center">
                                        <div class="auth-logo mb-4">
                                             <a href="index.php" class="logo-dark">
                                                  <img src="assets/images/logoC.png" height="24" alt="logo">
                                             </a>

                                             <a href="index.php" class="logo-light">
                                                  <img src="assets/images/logoC.png" height="24" alt="logo">
                                             </a>
                                        </div>

                                        <h2 class="fw-bold fs-24">Criar Conta</h2>

                                        <p class="text-muted mt-1 mb-4">Novo por aqui? Crie sua conta agora! Leva menos de um minuto.</p>

                                        <div>
                                             <?php if ($error): ?>
                                                  <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                             <?php endif; ?>
                                             
                                             <?php if ($success): ?>
                                                  <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                                             <?php endif; ?>

                                             <form action="" method="POST" class="authentication-form">
                                                  <div class="mb-3">
                                                       <label class="form-label" for="name">Nome Completo</label>
                                                       <input type="text" id="name" name="name" class="form-control" placeholder="Seu nome" value="<?php echo htmlspecialchars($_POST['name'] ?? '') ?>">
                                                  </div>
                                                  <div class="mb-3">
                                                       <label class="form-label" for="email">E-mail</label>
                                                       <input type="email" id="email" name="email" class="form-control" placeholder="Seu melhor e-mail" value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>">
                                                  </div>
                                                  <div class="mb-3">                                                      
                                                       <label class="form-label" for="password">Senha</label>
                                                       <div class="input-group">
                                                            <input type="password" id="password" name="password" class="form-control" placeholder="Crie uma senha segura">
                                                            <button type="button" class="btn btn-soft-secondary password-toggle" data-target="#password"><i class="bx bx-show"></i></button>
                                                       </div>
                                                  </div>

                                                  <div class="mb-1 text-center d-grid">
                                                       <button class="btn btn-soft-primary" type="submit">Cadastrar</button>
                                                  </div>
                                             </form>

                                             
                                        </div>

                                        <p class="mt-auto text-danger text-center">Já tem uma conta? <a href="auth-signin.php" class="text-dark fw-bold ms-1">Entrar</a></p>
                                   </div>
                              </div>
                         </div>
                    </div>

                    
               </div>
          </div>
     </div>

      <?php include 'partials/vendor-scripts.php' ?>
      <script>
      document.querySelectorAll('.password-toggle').forEach(function(el){
        el.addEventListener('click',function(){
          var sel=el.getAttribute('data-target');
          var inp=document.querySelector(sel);
          if(!inp) return;
          var show=inp.type==='password';
          inp.type=show?'text':'password';
          var ic=el.querySelector('i');
          if(ic){ ic.className=show?'bx bx-hide':'bx bx-show'; }
        });
      });
      </script>

</body>

</html>
