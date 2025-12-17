</div> <!-- content -->
</div> <!-- flex -->

<script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.26.10/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<script>
   // masks and validation
   document.addEventListener('input', function(e) {
      if (e.target.classList.contains('money')) {
         let v = e.target.value.replace(/\D/g, '');
         if (!v) {
            e.target.value = '';
            return;
         }
         v = (parseInt(v) / 100).toFixed(2);
         v = v.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
         e.target.value = v;
      }
      if (e.target.classList.contains('cpf')) {
         let v = e.target.value.replace(/\D/g, '');
         v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
         e.target.value = v.substring(0, 14);
      }
      if (e.target.classList.contains('cnpj')) {
         let v = e.target.value.replace(/\D/g, '');
         v = v.replace(/^(\d{2})(\d)/, '$1.$2').replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3').replace(/\.(\d{3})(\d)/, '.$1/$2').replace(/(\d{4})(\d)/, '$1-$2');
         e.target.value = v.substring(0, 18);
      }
      if (e.target.classList.contains('phone')) {
         let v = e.target.value.replace(/\D/g, '');
         v = v.replace(/^(\d{2})(\d)/g, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2');
         e.target.value = v.substring(0, 15);
      }
   });

   (() => {
      'use strict'
      const forms = document.querySelectorAll('.needs-validation')
      Array.from(forms).forEach(form => {
         form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
               event.preventDefault();
               event.stopPropagation();
            }
            form.classList.add('was-validated');
         }, false)
      })
   })();
</script>
</body>

</html>