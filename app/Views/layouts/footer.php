<?php
/**
 * Footer Layout - NetoNerd
 * Template de rodapé reutilizável
 */

$currentYear = date('Y');
?>

<!-- Main Content End -->

<!-- Footer -->
<footer class="footer-main">
    <div class="container">
        <!-- Footer Top -->
        <div class="row py-5">
            <!-- Sobre a Empresa -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="footer-title">
                    <img src="public/assets/images/logoNetoNerd.jpg" alt="NetoNerd" class="footer-logo mb-3">
                </h5>
                <p class="footer-text">
                    Soluções completas em tecnologia para pessoas físicas e jurídicas. 
                    Suporte técnico especializado com qualidade e agilidade.
                </p>
                <div class="social-links mt-4">
                    <a href="https://facebook.com" target="_blank" rel="noopener" class="social-link">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener" class="social-link">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://twitter.com" target="_blank" rel="noopener" class="social-link">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://linkedin.com" target="_blank" rel="noopener" class="social-link">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
            
            <!-- Links Rápidos -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="footer-title">Links Rápidos</h5>
                <ul class="footer-links">
                    <li><a href="index.php"><i class="fas fa-angle-right"></i> Home</a></li>
                    <li><a href="atendimento.php"><i class="fas fa-angle-right"></i> Atendimento</a></li>
                    <li><a href="planos.php"><i class="fas fa-angle-right"></i> Planos</a></li>
                    <li><a href="quemsomo.php"><i class="fas fa-angle-right"></i> Quem Somos</a></li>
                    <li><a href="contato.php"><i class="fas fa-angle-right"></i> Contato</a></li>
                </ul>
            </div>
            
            <!-- Serviços -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="footer-title">Nossos Serviços</h5>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-angle-right"></i> Suporte Técnico</a></li>
                    <li><a href="#"><i class="fas fa-angle-right"></i> Manutenção de TI</a></li>
                    <li><a href="#"><i class="fas fa-angle-right"></i> Desenvolvimento</a></li>
                    <li><a href="#"><i class="fas fa-angle-right"></i> Consultoria</a></li>
                    <li><a href="#"><i class="fas fa-angle-right"></i> Infraestrutura</a></li>
                </ul>
            </div>
            
            <!-- Contato -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="footer-title">Entre em Contato</h5>
                <ul class="footer-contact">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Itatiaia, Rio de Janeiro, BR</span>
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        <span>(24) 98888-8888</span>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <span>contato@netonerd.com.br</span>
                    </li>
                    <li>
                        <i class="fas fa-clock"></i>
                        <span>Seg - Sex: 8h às 18h</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-left mb-3 mb-md-0">
                    <p class="mb-0">
                        &copy; <?= $currentYear ?> <strong>NetoNerd</strong> - Todos os direitos reservados
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-right">
                    <ul class="footer-bottom-links">
                        <li><a href="#">Política de Privacidade</a></li>
                        <li><a href="#">Termos de Uso</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="backToTop" class="back-to-top" title="Voltar ao topo">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Custom JS -->
<script src="public/assets/js/main.js"></script>

<style>
/* ============================================================================
   FOOTER STYLES
   ============================================================================ */

.footer-main {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    color: #ffffff;
    margin-top: 80px;
}

.footer-logo {
    max-width: 150px;
    height: auto;
}

.footer-title {
    color: #ffffff;
    font-weight: 700;
    font-size: 1.2rem;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.footer-title::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 50px;
    height: 3px;
    background: linear-gradient(90deg, #007bff, #00d4ff);
}

.footer-text {
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.8;
    font-size: 0.95rem;
}

/* Social Links */
.social-links {
    display: flex;
    gap: 15px;
}

.social-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    border-radius: 50%;
    transition: all 0.3s ease;
    text-decoration: none;
}

.social-link:hover {
    background: #007bff;
    color: #ffffff;
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
}

/* Footer Links */
.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    font-size: 0.95rem;
}

.footer-links a i {
    margin-right: 8px;
    color: #007bff;
}

.footer-links a:hover {
    color: #ffffff;
    padding-left: 5px;
}

/* Footer Contact */
.footer-contact {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-contact li {
    display: flex;
    align-items: flex-start;
    margin-bottom: 15px;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.95rem;
}

.footer-contact i {
    color: #007bff;
    margin-right: 12px;
    margin-top: 3px;
    font-size: 1.1rem;
}

/* Footer Bottom */
.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding: 25px 0;
    margin-top: 30px;
}

.footer-bottom p {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
}

.footer-bottom-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: flex-end;
    gap: 20px;
}

.footer-bottom-links li {
    display: inline;
}

.footer-bottom-links a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.footer-bottom-links a:hover {
    color: #ffffff;
}

/* Back to Top Button */
.back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    box-shadow: 0 5px 20px rgba(0, 123, 255, 0.4);
    transition: all 0.3s ease;
    z-index: 1000;
}

.back-to-top:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 123, 255, 0.6);
}

.back-to-top i {
    font-size: 1.2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .footer-main {
        margin-top: 50px;
    }
    
    .footer-title::after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .footer-bottom-links {
        justify-content: center;
        margin-top: 15px;
    }
    
    .social-links {
        justify-content: center;
    }
}
</style>

<script>
// ============================================================================
// JAVASCRIPT GLOBAL
// ============================================================================

$(document).ready(function() {
    // Back to Top Button
    const backToTopButton = $('#backToTop');
    
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            backToTopButton.fadeIn();
        } else {
            backToTopButton.fadeOut();
        }
    });
    
    backToTopButton.click(function() {
        $('html, body').animate({
            scrollTop: 0
        }, 600);
        return false;
    });
    
    // Smooth Scroll para âncoras
    $('a[href*="#"]:not([href="#"])').click(function() {
        if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') 
            && location.hostname === this.hostname) {
            let target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 800);
                return false;
            }
        }
    });
    
    // Adiciona classe active no menu baseado na página atual
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    $('.navbar-nav a').each(function() {
        const href = $(this).attr('href');
        if (href && href.includes(currentPage)) {
            $(this).addClass('active');
        }
    });
    
    // Fecha o menu mobile ao clicar em um link
    $('.navbar-nav>li>a').on('click', function() {
        $('.navbar-collapse').collapse('hide');
    });
});

// Previne submit duplo em formulários
$('form').submit(function() {
    $(this).find('button[type="submit"]').prop('disabled', true);
});
</script>

</body>
</html> 