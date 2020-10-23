<footer class="footer ">
    <div class="container">
        <nav class="pull-left">
            <ul>
            </ul>
        </nav>
        <div class="copyright pull-right">
            &copy;
            <script>
                document.write(new Date().getFullYear())
            </script>, made with
            <i class="material-icons">favorite
            </i> by
            <a href="https://www.trioangle.com" target="_blank">Trioangle
            </a> for a better web.
        </div>
    </div>
</footer>
<div class="modal fade" id="payout_preference" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    @lang('admin_messages.payout_details')
                </h3>
            </div>
            <div class="modal-body">
                <table class="table" id="payout_details">
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">
                    @lang('admin_messages.close')
                </button>
            </div>
        </div>
    </div>
</div>