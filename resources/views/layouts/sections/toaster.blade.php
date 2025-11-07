<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<script>
  var notyf = new Notyf();
  // success message popup notification
  @if(session()->has('success'))
  notyf.success("{{ session()->get('success') }}");
  @endif

  // info message popup notification
  @if(session()->has('info'))
  notyf.info("{{ session()->get('info') }}");
  @endif

  // warning message popup notification
  @if(session()->has('warning'))
  notyf.warning("{{ session()->get('warning') }}");
  @endif

  // error message popup notification
  @if(session()->has('error'))
  notyf.error("{{ session()->get('error') }}");
  @endif

  @if ($errors->any())
  let errorMessages = `{!! implode('<br>', $errors->all()) !!}`;
  showErrorSwalHtml(errorMessages);
  @endif


  function showSuccessToast(message) {
    notyf.success(message);
  }

  function showErrorToast(message) {
    notyf.error(message);
  }

  function showInfoToast(message) {
    notyf.info(message);
  }

  function showWarningToast(message) {
    notyf.warning(message);
  }

  function showSuccessSwal(message) {
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: message,
      customClass: {
        confirmButton: 'btn btn-success'
      }
    });
  }

  function showInfoSwal(message) {
    Swal.fire({
      icon: 'info',
      title: 'Info',
      text: message,
      customClass: {
        confirmButton: 'btn btn-info'
      }
    });
  }

  function showWarningSwal(message) {
    Swal.fire({
      icon: 'warning',
      title: 'Warning',
      text: message,
      customClass: {
        confirmButton: 'btn btn-warning'
      }
    });
  }

  function showErrorSwal(message) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: message,
      customClass: {
        confirmButton: 'btn btn-danger'
      }
    });
  }

  function showErrorSwalHtml(message) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      html: message,
      customClass: {
        confirmButton: 'btn btn-danger'
      }
    });
  }

</script>
