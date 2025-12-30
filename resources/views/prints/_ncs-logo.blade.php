{{-- NCS Logo/Emblem for documents that require it --}}
<div class="ncs-logo-container" style="text-align: center; margin-bottom: 20px;">
    <div style="display: inline-block; position: relative;">
        {{-- NCS Emblem SVG --}}
        <svg width="120" height="140" viewBox="0 0 120 140" xmlns="http://www.w3.org/2000/svg" style="display: block; margin: 0 auto;">
            {{-- Green and White Striped Bar --}}
            <rect x="10" y="10" width="100" height="8" fill="#008000"/>
            <rect x="10" y="12" width="100" height="4" fill="#FFFFFF"/>
            
            {{-- Five-pointed Star --}}
            <g transform="translate(60, 50)">
                {{-- Star outer shape --}}
                <path d="M 0,-30 L 9,-9 L 30,-9 L 15,3 L 24,24 L 0,12 L -24,24 L -15,3 L -30,-9 L -9,-9 Z" 
                      fill="#FFFFFF" stroke="#CCCCCC" stroke-width="0.5"/>
                {{-- All-seeing eye in center --}}
                <ellipse cx="0" cy="0" rx="4" ry="6" fill="#000000"/>
                <ellipse cx="0" cy="-1" rx="2" ry="3" fill="#FFFFFF"/>
            </g>
            
            {{-- Staff/Baton on left --}}
            <line x1="25" y1="60" x2="25" y2="90" stroke="#000000" stroke-width="3"/>
            <line x1="25" y1="60" x2="20" y2="65" stroke="#000000" stroke-width="2"/>
            
            {{-- Rifle/Musket on right --}}
            <line x1="95" y1="60" x2="95" y2="90" stroke="#000000" stroke-width="3"/>
            <line x1="95" y1="60" x2="100" y2="65" stroke="#000000" stroke-width="2"/>
            <line x1="95" y1="90" x2="100" y2="95" stroke="#000000" stroke-width="2"/>
            
            {{-- Green base --}}
            <path d="M 20,90 Q 30,95 40,92 Q 50,89 60,92 Q 70,89 80,92 Q 90,95 100,90 L 100,100 L 20,100 Z" fill="#008000"/>
            
            {{-- Yellow flowers --}}
            <circle cx="45" cy="95" r="3" fill="#FFD700"/>
            <circle cx="55" cy="95" r="3" fill="#FFD700"/>
            <circle cx="45" cy="100" r="3" fill="#FFD700"/>
            <circle cx="55" cy="100" r="3" fill="#FFD700"/>
            
            {{-- Red Eagle at top --}}
            <g transform="translate(60, 5)">
                <path d="M -15,0 Q -10,-5 0,-8 Q 10,-5 15,0 Q 12,2 8,3 Q 4,5 0,4 Q -4,5 -8,3 Q -12,2 -15,0" 
                      fill="#DC143C" stroke="#8B0000" stroke-width="0.5"/>
                <path d="M -8,3 Q -4,1 0,2 Q 4,1 8,3" stroke="#8B0000" stroke-width="0.5" fill="none"/>
            </g>
            
            {{-- Banner at bottom --}}
            <path d="M 10,110 Q 60,105 110,110 L 110,125 Q 60,120 10,125 Z" fill="#000000"/>
            <text x="60" y="118" text-anchor="middle" fill="#FFFFFF" font-size="6" font-weight="bold" font-family="Arial, sans-serif">NIGERIA CUSTOMS SERVICE</text>
            <text x="60" y="123" text-anchor="middle" fill="#FFFFFF" font-size="5" font-weight="bold" font-family="Arial, sans-serif">JUSTICE AND HONESTY</text>
        </svg>
    </div>
</div>

