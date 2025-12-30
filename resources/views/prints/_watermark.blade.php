{{-- Watermark for all print documents --}}
<style>
    body::before {
        content: "NCS Management Information System (MIS)";
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 48pt;
        font-weight: bold;
        color: #FFFACD; /* Lemon color */
        opacity: 0.15;
        z-index: -1;
        pointer-events: none;
        white-space: nowrap;
        font-family: 'Times New Roman', serif;
    }
    
    @media print {
        body::before {
            opacity: 0.12;
        }
    }
</style>



