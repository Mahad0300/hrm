// user/assets/js/announcements.js

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('announcementsContainer');
    if (!container) return;

    fetchAnnouncements();

    async function fetchAnnouncements() {
        try {
            const response = await fetch('assets/api/announcement_handler.php');
            const res = await response.json();

            if (res.status === 'success') {
                renderAnnouncements(res.data);
            } else {
                console.error('Failed to fetch announcements:', res.message);
                showEmptyState('Error loading announcements.');
            }
        } catch (err) {
            console.error('Error:', err);
            showEmptyState('Could not connect to server.');
        }
    }

    function getEmoji(type) {
        const t = (type || '').toUpperCase();
        if (t === 'IMPORTANT') return '🚨';
        if (t === 'CELEBRATION') return '🎉';
        if (t === 'HOLIDAY') return '📅';
        if (t === 'MEETING') return '🤝';
        return '📢';
    }

    function renderAnnouncements(data) {
        container.innerHTML = '';

        if (!data || data.length === 0) {
            showEmptyState();
            return;
        }

        data.forEach(ann => {
            const article = document.createElement('article');
            article.className = 'ann-simple-card';

            const authorImg = ann.profile_pic ? `../${ann.profile_pic}` : '../images/profile-image/default-avatar.svg';
            const firstChar = (ann.author_name || 'S').charAt(0);

            article.innerHTML = `
                <header class="ann-simple-card__head">
                    <div class="ann-simple-card__head-main">
                        <span class="ann-simple-card__icon" aria-hidden="true">${getEmoji(ann.type)}</span>
                        <h2 class="ann-simple-card__title">${escapeHtml(ann.title)}</h2>
                    </div>
                    <span class="ann-simple-card__badge" data-type="${ann.type}">${ann.type}</span>
                </header>

                <div class="ann-simple-card__body">
                    ${ann.content}
                </div>
            
                <footer class="ann-simple-card__foot">
                    <div class="ann-simple-card__by">
                        <img src="${authorImg}" class="ann-simple-card__avatar-img" width="44" height="44" alt="${escapeHtml(ann.author_name)}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <span class="ann-simple-card__avatar-init ann-simple-card__avatar-init--lime" style="display:none;" aria-hidden="true">${firstChar}</span>
                        <div class="ann-simple-card__by-text">
                            <span class="ann-simple-card__by-label">Posted by</span>
                            <span class="ann-simple-card__by-name">${escapeHtml(ann.author_name)}</span>
                        </div>
                    </div>
                    <time class="ann-simple-card__date" datetime="${ann.date_iso}">${ann.formatted_date}</time>
                </footer>
            `;
            container.appendChild(article);
        });

        // Initialize Lucide icons if any were added
        if (window.lucide) lucide.createIcons();
    }

    function showEmptyState(msg = 'No current updates for your department.') {
        container.innerHTML = `
            <div class="empty-state-wrapper py-60">
                <div class="empty-state-icon mb-20">
                    <i data-lucide="megaphone-off" size="48" class="text-light"></i>
                </div>
                <h3 class="font-18 font-600 text-dark mb-8">All Caught Up!</h3>
                <p class="text-light font-14">${msg}</p>
            </div>
        `;
        if (window.lucide) lucide.createIcons();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
