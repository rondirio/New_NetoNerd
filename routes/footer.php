<!-- Footer -->
<!--    <footer class="bg-primary text-white text-center py-4 mt-5">-->
<!--  <div class="container">-->
    <!-- Copyright -->
<!--    <p class="mb-2">© 2025 Four_BA - Todos os direitos reservados</p>-->
    
    <!-- Links do Site -->
<!--    <div class="footer-links mb-3">-->
<!--      <a href="#atendimento" class="text-white mx-3">Atendimento</a>-->
<!--      <a href="#planos" class="text-white mx-3">Planos</a>-->
<!--      <a href="#contato" class="text-white mx-3">Contato</a>-->
<!--      <a href="#login" class="text-white mx-3">Login</a>-->
<!--    </div>-->
    
    <!-- Social Links -->
<!--    <div class="social-links">-->
<!--      <a href="https://facebook.com" target="_blank" class="text-white mx-3">-->
<!--        <i class="bg-dark fab fa-facebook fa-2x"></i>-->
<!--      </a>-->
<!--      <a href="https://twitter.com" target="_blank" class="text-white mx-3">-->
<!--        <i class="bg-dark fab fa-twitter fa-2x"></i>-->
<!--      </a>-->
<!--      <a href="https://instagram.com" target="_blank" class="text-white mx-3">-->
<!--        <i class="bg-dark fab fa-instagram fa-2x"></i>-->
<!--      </a>-->
<!--    </div>-->
<!--  </div>-->
<!--</footer-->


<?php
// Root do projeto = pasta pai de routes/ (onde este arquivo está)
// Funciona em qualquer página que inclua o footer, em dev e produção
$_footerDocRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
$_footerProjDir = str_replace('\\', '/', dirname(__DIR__)); // c:/xampp/htdocs/NetoNerd/New_NetoNerd-main
$_footerRoot    = str_replace($_footerDocRoot, '', $_footerProjDir); // /NetoNerd/New_NetoNerd-main
$_footerProto   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_footerBase    = $_footerProto . '://' . $_SERVER['HTTP_HOST'] . $_footerRoot;
// ex: http://localhost/NetoNerd/New_NetoNerd-main  ou  https://netonerd.com.br.br
?>
 <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-section">
                        <h5 class="footer-title">NetoNerd</h5>
                        <p>Soluções tecnológicas profissionais que transformam negócios. Suporte, desenvolvimento e consultoria em TI.</p>
                        <div class="social-links mt-3">
                            <a href="https://facebook.com/netonerd" target="_blank" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://instagram.com/netonerd" target="_blank" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://twitter.com/netonerd" target="_blank" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://linkedin.com/company/netonerd" target="_blank" title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <div class="footer-section">
                        <h5 class="footer-title">Produtos</h5>
                        <ul class="footer-links">
                            <li><a href="<?= $_footerBase ?>/publics/produtos.php?id=myhealth">MyHealth</a></li>
                            <li><a href="<?= $_footerBase ?>/publics/produtos.php?id=escritorius">Escritorius</a></li>
                            <li><a href="<?= $_footerBase ?>/publics/produtos.php?id=stylemanager">Style Manager</a></li>
                            <li><a href="<?= $_footerBase ?>/publics/produtos.php?id=pj">NetoNerd PJ</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <div class="footer-section">
                        <h5 class="footer-title">Empresa</h5>
                        <ul class="footer-links">
                            <li><a href="<?= $_footerBase ?>/publics/quemsomo.php">Sobre Nós</a></li>
                            <li><a href="<?= $_footerBase ?>/publics/atendimento.php">Atendimento</a></li>
                            <li><a href="<?= $_footerBase ?>/publics/planos.php">Planos</a></li>
                            <li><a href="<?= $_footerBase ?>/publics/contato.php">Contato</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-4">
                    <div class="footer-section">
                        <h5 class="footer-title">Contato</h5>
                        <ul class="footer-links" style="list-style: none; padding: 0;">
                            <li><i class="fas fa-map-marker-alt"></i> Teresópolis</li>
                            <li><i class="fas fa-map-marker-alt"></i> Araruama</li>
                            <li><i class="fas fa-map-marker-alt"></i> Saquarema</li>
                            <li><i class="fas fa-phone"></i> (21) 97739-5867</li>
                            <li><i class="fas fa-envelope"></i> netonerdinterno@gmail.com</li>
                            <li><i class="fas fa-clock"></i> Seg-Sex: 9h-18h</li>
                        </ul>
                       
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Neto Nerd Soluções Digitais LTDA. &mdash; CNPJ: 65.663.425/0001-26. Todos os direitos reservados.</p>
                <p class="mb-0">
                    <a href="<?= $_footerBase ?>/publics/termos.php" style="color: rgba(255,255,255,0.6); margin: 0 10px;">Termos de Uso</a> |
                    <a href="<?= $_footerBase ?>/publics/privacidade.php" style="color: rgba(255,255,255,0.6); margin: 0 10px;">Política de Privacidade</a>
                </p>
            </div>
        </div>
    </footer>