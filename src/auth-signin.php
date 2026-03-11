<?php include 'services/session.php'; ?>
<?php require_once 'services/auth.php'; ?>
<?php
$_SESSION['error'] = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $input = trim($_POST['username']);
    $password = $_POST['password'];
    if ($input === '' || $password === '') {
        $_SESSION['error'] = 'Informe usuário e senha';
    } else {
        $username = strtolower($input);
        if (function_exists('banco_mock_enabled') && banco_mock_enabled()) {
            if ($password === 'mudar123') {
                $mail = strpos($username, '@') !== false ? $username : ($username . '@demo.local');
                $role = ($username === 'admin' || $mail === 'admin@empresa.com') ? 'admin' : 'user';
                $sessUser = ['nome' => $username, 'email' => $mail, 'papel' => $role, 'ativo' => 1];
                setSessionUser($sessUser);
                header('Location: index.php');
                exit;
            } else {
                $_SESSION['error'] = 'Credenciais inválidas. Verifique e tente novamente.';
            }
        } else {
            $ok = login($username, $password);
            if ($ok) {
                header('Location: index.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br" class="h-100">

<head>
     <?php
     $subTitle = "Entrar";
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

                                        <h2 class="fw-bold fs-24">Entrar</h2>

                                        <p class="text-muted mt-1 mb-4">Informe seu usuário e senha do computador para acessar.</p>

                                        <div class="mb-5">
                                             <form method="POST" class="authentication-form">
                                                  <div class="mb-3">
                                                       <label class="form-label" for="example-username">Usuário</label>
                                                       <input type="text" id="example-username" name="username" class="form-control bg-" placeholder="Digite seu usuário" value="<?php echo(htmlspecialchars($_POST['username'] ?? "")) ?>">
                                                       <span class="text-danger"><?php echo $_SESSION['error'] ?></span>
                                                  </div>
                                                  <div class="mb-3">
                                                       <!-- link de recuperar removido -->
                                                       <label class="form-label" for="example-password">Senha</label>
                                                       <div class="input-group">
                                                            <input type="password" id="example-password" name="password" class="form-control" placeholder="Digite sua senha">
                                                            <button type="button" class="btn btn-soft-secondary password-toggle" data-target="#example-password"><i class="bx bx-show"></i></button>
                                                       </div>
                                                  </div>
                                                 
                                             <div class="mb-1 text-center d-grid">
                                                  <button class="btn btn-soft-primary" type="submit">Entrar</button>
                                             </div>
                                         </form>
                                         </div>

                                        
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
