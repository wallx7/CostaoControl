<div class="main-nav">
     <!-- Sidebar Logo -->
     <div class="logo-box">
          <a href="index.php" class="logo-dark">
               <img src="assets/images/logoC.png" class="logo-lg" alt="logo">
               <img src="assets/images/logoC.png" class="logo-sm" alt="logo">
          </a>

          <a href="index.php" class="logo-light">
               <img src="assets/images/logoC.png" class="logo-lg" alt="logo">
               <img src="assets/images/logoC.png" class="logo-sm" alt="logo">
          </a>
     </div>

     <!-- Menu Toggle Button (sm-hover) -->
     <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
          <iconify-icon icon="solar:double-alt-arrow-right-bold-duotone" class="button-sm-hover-icon"></iconify-icon>
     </button>

     <div class="scrollbar" data-simplebar>
          <ul class="navbar-nav" id="navbar-nav">

               <li class="menu-title">Geral</li>

               <li class="nav-item">
                    <a class="nav-link" href="index.php">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:widget-5-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Painel </span>
                    </a>
                </li>


               <li class="nav-item">
                    <a class="nav-link" href="dashboard-ia.php">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:cpu-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Dashboard IA </span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarInventarioTI" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarInventarioTI">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:monitor-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Inventário de TI </span>
                    </a>
                    <div class="collapse show" id="sidebarInventarioTI">
                        <ul class="nav sub-navbar-nav">
                              
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="inventario-categorias.php">Categorias</a>
                              </li>
                              
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="equipamentos-add.php">Cadastrar Equipamento</a>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarCategory" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCategory">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:clipboard-list-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Termos </span>
                    </a>
                    <div class="collapse" id="sidebarCategory">
                         <ul class="nav sub-navbar-nav">
                             <li class="sub-nav-item">
                                  <a class="sub-nav-link" href="termos-list.php">Termos</a>
                             </li>
                             <li class="sub-nav-item">
                                  <a class="sub-nav-link" href="termo-assinar.php">Assinar</a>
                             </li>
                             <li class="sub-nav-item">
                                  <a class="sub-nav-link" href="assinaturas-antigas.php">Assinaturas antigas</a>
                             </li>
                             <li class="sub-nav-item">
                                  <a class="sub-nav-link" href="assinaturas-list.php">Assinaturas</a>
                             </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarInventory" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarInventory">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Estoque </span>
                    </a>
                    <div class="collapse" id="sidebarInventory">
                         <ul class="nav sub-navbar-nav">

                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="inventory-warehouse.php">Armazém</a>
                              </li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" href="inventory-received-orders.php">Bancada Virtual</a>
                              </li>
                         </ul>
                    </div>
                 </li>

                

               

                

                <li class="nav-item">
                     <a class="nav-link menu-arrow" href="#sidebarUsers" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarUsers">
                         <span class="nav-icon">
                              <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                         </span>
                         <span class="nav-text"> Usuários </span>
                    </a>
                     <div class="collapse" id="sidebarUsers">
                         <ul class="nav sub-navbar-nav">
                             <li class="sub-nav-item">
                                  <a class="sub-nav-link" href="usuarios-ti.php">TI</a>
                             </li>
                             <li class="sub-nav-item">
                                  <a class="sub-nav-link" href="colaboradores-posse.php">Colaboradores</a>
                             </li>
                         </ul>
                     </div>
                 </li>

                 <li class="nav-item">
                      <a class="nav-link" href="mapa.php">
                           <span class="nav-icon">
                                <iconify-icon icon="solar:map-point-bold-duotone"></iconify-icon>
                           </span>
                           <span class="nav-text"> Mapa </span>
                      </a>
                 </li>

                

               

               
                 </ul>
            </div>
       </div>
