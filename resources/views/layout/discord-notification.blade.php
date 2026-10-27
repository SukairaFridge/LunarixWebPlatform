@if(session('discord_notice'))
@if(request()->is('administration*'))
<script>
    window.alert(@json(session('discord_notice')));
</script>
@else
<script>
    $(function () {
        Neurotic.GenericModal.open("Notice", "/images/Icons/img-alert.png", @json(session('discord_notice')), null, false);
    });
</script>
@endif
@endif