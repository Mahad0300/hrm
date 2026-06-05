/**
 * IT Support Ticketing System JS
 * Handles UI interactions, API calls, and Real-Time Chat feel
 */

const ITSupport = (() => {
    const apiUrl = '../includes/api/it-support-handler.php';

    const isHelpdeskAdmin = () => ['Admin', 'HR'].includes(IT_USER.role);

    const escapeHtml = (str) => {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    };
    let cachedTickets = [];
    let currentFilter = 'All';
    let currentTicketId = null;
    let lastMessageCount = 0;

    const init = () => {
        bindEvents();
        fetchTickets();
        showPlaceholder();
        
        // Optional: Polling for real-time updates (every 3 seconds)
        setInterval(() => {
            fetchTicketsSilent();
            if (currentTicketId) {
                loadTicketDetails(currentTicketId, true);
            }
        }, 8000);
    };

    const fetchTickets = () => {
        $.ajax({
            url: apiUrl,
            method: 'GET',
            data: { action: 'get_tickets' },
            dataType: 'json',
            success: (res) => {
                if (res.success) {
                    cachedTickets = res.data;
                    applyFilters();
                } else {
                    $('#ticket-list-container').html('<div class="p-4 text-center text-danger small">Failed to load tickets.</div>');
                }
            },
            error: () => {
                $('#ticket-list-container').html('<div class="p-4 text-center text-danger small">Server error.</div>');
            }
        });
    };

    const fetchTicketsSilent = () => {
        $.ajax({
            url: apiUrl,
            method: 'GET',
            data: { action: 'get_tickets' },
            dataType: 'json',
            success: (res) => {
                if (res.success) {
                    // Only re-render the list if the data actually changed
                    if (JSON.stringify(cachedTickets) !== JSON.stringify(res.data)) {
                        cachedTickets = res.data;
                        applyFilters();
                    }
                }
            }
        });
    };

    const bindEvents = () => {
        // Tab switching
        $(document).on('click', '.it-filter-tab', function() {
            $('.it-filter-tab').removeClass('active');
            $(this).addClass('active');
            currentFilter = $(this).data('filter');
            applyFilters();
        });

        // Search
        $(document).on('keyup', '#it-search-input', function() {
            applyFilters();
        });

        // Create Ticket Button
        $(document).on('click', '#btn-create-ticket', function() {
            showCreateForm();
        });

        // Cancel Create
        $(document).on('click', '#btn-cancel-create', function() {
            showPlaceholder();
        });

        // Category Tab selection
        $(document).on('click', '.it-cat-tab', function() {
            $('.it-cat-tab').removeClass('active');
            $(this).addClass('active');
            const cat = $(this).data('cat');
            $('#selected-category').val(cat);
            if(cat === 'Other') {
                $('#other-cat-group').slideDown();
            } else {
                $('#other-cat-group').slideUp();
                $('#custom-category').val('');
            }
        });

        // Submit Ticket
        $(document).on('click', '#btn-submit-ticket', function() {
            const formData = new FormData(document.getElementById('ticket-form'));
            
            const subject = formData.get('subject').trim();
            const desc = formData.get('description').trim();
            if(!subject || !desc) {
                Swal.fire('Error', 'Please fill in all required fields.', 'error');
                return;
            }

            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');

            $.ajax({
                url: apiUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: (res) => {
                    if (res.success) {
                        Swal.fire('Success', 'Ticket created successfully!', 'success');
                        fetchTickets();
                        showPlaceholder();
                    } else {
                        Swal.fire('Error', res.message || 'Failed to create ticket', 'error');
                        btn.prop('disabled', false).text('Submit Ticket');
                    }
                },
                error: () => {
                    Swal.fire('Error', 'Server error while creating ticket', 'error');
                    btn.prop('disabled', false).text('Submit Ticket');
                }
            });
        });

        // Ticket List Item Click
        $(document).on('click', '.it-ticket-item', function() {
            const id = $(this).data('id');
            loadTicketDetails(id);
        });

        // Back to list placeholder
        $(document).on('click', '#btn-back-to-list', function() {
            showPlaceholder();
        });
        
        // Status change button (Resolve / Close)
        $(document).on('click', '.btn-change-status', function() {
            const status = $(this).data('status');
            
            if (status === 'Resolved' || status === 'Closed') {
                const title = status === 'Resolved' ? 'Mark as Resolved?' : 'Close Ticket?';
                const text = status === 'Resolved' ? 'Are you sure this ticket has been completely resolved?' : 'Are you sure you want to close this ticket without resolving it?';
                const confirmBtn = status === 'Resolved' ? 'Yes, Resolve It' : 'Yes, Close It';
                const confirmColor = status === 'Resolved' ? '#10B981' : '#EF4444';
                const apiAction = status === 'Resolved' ? 'resolve_ticket' : 'close_ticket';

                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: confirmBtn,
                    confirmButtonColor: confirmColor
                }).then((result) => {
                    if (result.isConfirmed) {
                        performAction(apiAction, { ticket_id: currentTicketId });
                    }
                });
            }
        });

        // Reopen Ticket
        $(document).on('click', '#btn-reopen-ticket', function() {
            Swal.fire({
                title: 'Re-open Ticket?',
                text: 'The issue is not fixed?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Re-open',
                confirmButtonColor: '#EF4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    performAction('reopen_ticket', { ticket_id: currentTicketId });
                }
            });
        });

        // Claim Ticket
        $(document).on('click', '#btn-claim-ticket', function() {
            performAction('claim_ticket', { ticket_id: currentTicketId });
        });
        
        // Handover Ticket
        $(document).on('click', '#btn-handover-ticket', function() {
            // First fetch IT staff members
            $.ajax({
                url: apiUrl,
                method: 'GET',
                data: { action: 'get_it_staff' },
                dataType: 'json',
                success: (res) => {
                    if (res.success && res.data.length > 0) {
                        const options = {};
                        res.data.forEach(s => {
                            if (s.id != IT_USER.emp_id) { // Don't allow handing over to self
                                options[s.id] = s.name;
                            }
                        });

                        if (Object.keys(options).length === 0) {
                            Swal.fire('Error', 'No other IT staff members available for handover.', 'error');
                            return;
                        }

                        Swal.fire({
                            title: 'Handover Ticket',
                            input: 'select',
                            inputOptions: options,
                            inputPlaceholder: 'Select IT Staff Member',
                            showCancelButton: true,
                            confirmButtonText: 'Handover',
                            confirmButtonColor: 'var(--it-primary)'
                        }).then((result) => {
                            if (result.isConfirmed && result.value) {
                                performAction('handover_ticket', { 
                                    ticket_id: currentTicketId, 
                                    new_assignee_id: result.value 
                                });
                            }
                        });
                    } else {
                        Swal.fire('Error', 'Could not load IT staff members.', 'error');
                    }
                }
            });
        });
        
        // Chat Submit
        $(document).on('click', '#btn-send-message', function() {
            sendMessage();
        });

        $(document).on('keypress', '#chat-message-input', function(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    };

    const sendMessage = () => {
        const input = $('#chat-message-input');
        const msg = input.val().trim();
        if(msg && currentTicketId) {
            const isInternal = $('#is-internal-note').is(':checked');
            
            const btn = $('#btn-send-message');
            btn.prop('disabled', true);
            
            $.ajax({
                url: apiUrl,
                method: 'POST',
                data: {
                    action: 'send_message',
                    ticket_id: currentTicketId,
                    message: msg,
                    is_internal: isInternal ? 'true' : 'false'
                },
                dataType: 'json',
                success: (res) => {
                    if (res.success) {
                        input.val('');
                        if (isInternal) $('#is-internal-note').prop('checked', false);
                        loadTicketDetails(currentTicketId, true); // silent refresh chat
                    } else {
                        Swal.fire('Error', res.message || 'Failed to send message', 'error');
                    }
                    btn.prop('disabled', false);
                },
                error: () => {
                    btn.prop('disabled', false);
                }
            });
        }
    };

    const performAction = (action, data) => {
        data.action = action;
        $.ajax({
            url: apiUrl,
            method: 'POST',
            data: data,
            dataType: 'json',
            success: (res) => {
                if (res.success) {
                    Swal.fire('Success', 'Action completed successfully', 'success');
                    fetchTickets();
                    loadTicketDetails(currentTicketId);
                } else {
                    Swal.fire('Error', res.message || 'Action failed', 'error');
                }
            },
            error: () => {
                Swal.fire('Error', 'Server error', 'error');
            }
        });
    };

    const applyFilters = () => {
        const query = $('#it-search-input').val().toLowerCase();
        let html = '';
        
        cachedTickets.forEach(t => {
            if(currentFilter !== 'All' && t.status !== currentFilter) return;
            const haystack = (t.subject + ' ' + (t.user || '') + ' ' + (t.category || '')).toLowerCase();
            if(query && haystack.indexOf(query) === -1) return;
            
            const activeClass = currentTicketId == t.id ? 'active' : '';
            const unreadBadge = t.unread_count > 0 ? `<span class="it-ticket-unread-badge" style="background: #EF4444; color: white; border-radius: 10px; padding: 3px 8px; font-size: 11px; font-weight: 600; line-height: 1;">${t.unread_count}</span>` : '';
            
            html += `
                <div class="it-ticket-item ${activeClass}" data-id="${t.id}">
                    <div class="it-ticket-user" style="font-size: 13.5px; font-weight: 600; color: #1E293B; margin-bottom: 4px;">
                        ${escapeHtml(t.user)}
                    </div>
                    <div class="it-ticket-item-header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px;">
                        <h6 class="it-ticket-title" style="margin: 0; font-size: 12.5px; font-weight: 500; line-height: 1.4; color: #64748B; white-space: normal; text-overflow: unset; max-width: unset;">#${t.id} - ${escapeHtml(t.subject)} - ${escapeHtml(t.category)}</h6>
                        <span class="it-ticket-status status-${t.status.toLowerCase()}" style="flex-shrink: 0; margin-top: 2px;">${t.status}</span>
                    </div>
                    <div class="it-ticket-meta" style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-top: 6px;">
                        <span><i class="far fa-clock"></i> ${t.date}</span>
                        ${unreadBadge}
                    </div>
                </div>
            `;
        });
        
        if(html === '') html = '<div class="p-4 text-center text-muted small">No tickets found.</div>';
        $('#ticket-list-container').html(html);
    };

    const loadTicketDetails = (id, silentUpdate = false) => {
        if (!silentUpdate) currentTicketId = id;
        
        $.ajax({
            url: apiUrl,
            method: 'GET',
            data: { action: 'get_ticket_details', ticket_id: id },
            dataType: 'json',
            success: (res) => {
                if (res.success) {
                    if (silentUpdate) {
                        updateTicketDetailsSilent(res.data);
                    } else {
                        renderTicketDetails(res.data);
                        applyFilters(); // refresh active state
                    }
                } else {
                    Swal.fire('Error', res.message || 'Could not load ticket.', 'error');
                }
            },
            error: (xhr) => {
                let msg = 'Could not load ticket. Check database column admin_unread or server logs.';
                try {
                    const j = JSON.parse(xhr.responseText);
                    if (j.message) msg = j.message;
                } catch (e) { /* ignore */ }
                if (!silentUpdate) {
                    Swal.fire('Error', msg, 'error');
                }
            }
        });
    };

    const updateTicketDetailsSilent = (ticket) => {
        // Only update if there are new messages
        if (ticket.messages.length !== lastMessageCount) {
            let messagesHtml = '';
            ticket.messages.forEach(m => {
                // If it's an old test "Ticket resolved by agent", skip it to clean the UI
                if (m.text.includes('Ticket resolved by agent.') || m.text.includes('Ticket closed by agent.')) return;

                if (m.is_system || m.text.startsWith('Ticket Re-opened.')) {
                    messagesHtml += `
                        <div class="text-center my-3">
                            <span style="font-size: 11px; background: rgba(108, 76, 241, 0.1); color: #6C4CF1; padding: 6px 16px; border-radius: 20px; font-weight: 600; display: inline-block;">
                                <i class="fas fa-info-circle"></i> ${m.text}
                            </span>
                        </div>
                    `;
                } else {
                    const isRight = m.sender_id == IT_USER.emp_id; 
                    const bubbleClass = isRight ? 'right' : 'left';
                    const internalClass = m.is_internal ? 'it-internal-bubble' : '';
                    const internalBadge = m.is_internal ? '<span class="it-internal-badge">Internal Note</span>' : '';

                    const senderNameHtml = !isRight ? `<div style="font-size: 10px; color: #a4b0be; margin-bottom: 2px; font-weight: 600;">${m.sender}</div>` : '';

                    messagesHtml += `
                        <div class="it-chat-bubble ${bubbleClass} ${internalClass}">
                            ${senderNameHtml}
                            ${m.text}
                            <span class="it-chat-meta">${m.time} ${internalBadge}</span>
                        </div>
                    `;
                }
            });

            $('#chat-messages-box').html(messagesHtml);
            scrollToBottom();
            lastMessageCount = ticket.messages.length;
        }
        
        // Also update ticket status badge silently if it changed
        const statusSpan = $('.it-ticket-status');
        if (statusSpan.length) {
            // Update the status on the active ticket list item silently
            const activeTicketItem = $(`.it-ticket-item[data-id="${ticket.id}"] .it-ticket-status`);
            if (activeTicketItem.text() !== ticket.status) {
                applyFilters(); // refresh list to show updated status/assignee
            }
        }

        // Update Handled By Badge
        const assignedText = ticket.assigned_to_name ? ticket.assigned_to_name : 'Unassigned';
        const assignedBadgeClass = ticket.assigned_to_name ? 'text-primary' : 'text-danger';
        
        $('#ticket-handled-by-badge').removeClass('text-primary text-danger').addClass(assignedBadgeClass);
        $('#ticket-handled-by-text').text(assignedText);

        // Update Header Actions
        const isIT = IT_USER.is_it_staff;
        $('#ticket-header-actions').html(getHeaderActionsHtml(ticket, isIT));

        // Update Input Area if permission or status changes
        const isCreator = ticket.employee_id == IT_USER.emp_id;
        const isAssignedAgent = ticket.assigned_to == IT_USER.emp_id;
        const isAdmin = isHelpdeskAdmin();
        const canChat = (ticket.status !== 'Resolved' && ticket.status !== 'Closed') && (isCreator || isAssignedAgent || isAdmin);
        
        const hasTextarea = $('#chat-message-input').length > 0;
        
        if (canChat !== hasTextarea) {
            const internalToggleHtml = isIT ? `
                <div class="it-chat-internal-toggle">
                    <input type="checkbox" id="is-internal-note">
                    <label for="is-internal-note" class="m-0 cursor-pointer text-warning font-600">Mark as Internal Note (Hidden from employee)</label>
                </div>
            ` : '';
            $('#ticket-input-area-wrapper').html(getInputAreaHtml(ticket, internalToggleHtml));
        }
    };

    const getHeaderActionsHtml = (ticket, isIT) => {
        let headerActionsHtml = '';
        if(isIT && ticket.status !== 'Resolved' && ticket.status !== 'Closed') {
            if(!ticket.assigned_to) {
                headerActionsHtml = `
                    <button type="button" id="btn-claim-ticket" style="background: #6C4CF1; border: 1px solid #6C4CF1; color: white; padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.opacity='0.9';" onmouseout="this.style.opacity='1';">
                        <i class="fas fa-hand-pointer"></i> Claim Ticket
                    </button>
                `;
            } else {
                const isAssignedAgent = ticket.assigned_to == IT_USER.emp_id;
                const isAdmin = isHelpdeskAdmin();
                if (isAssignedAgent || isAdmin) {
                    headerActionsHtml = `
                        <div style="display: flex; gap: 8px;">
                            <button type="button" id="btn-handover-ticket" style="background: transparent; border: 1px solid #6C4CF1; color: #6C4CF1; padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(108, 76, 241, 0.1)';" onmouseout="this.style.background='transparent';">
                                <i class="fas fa-exchange-alt"></i> Handover
                            </button>
                            <button type="button" class="btn-change-status" data-status="Resolved" style="background: #10B981; border: 1px solid #10B981; color: white; padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.opacity='0.9';" onmouseout="this.style.opacity='1';">
                                <i class="fas fa-check"></i> Mark Resolved
                            </button>
                            <button type="button" class="btn-change-status" data-status="Closed" style="background: #EF4444; border: 1px solid #EF4444; color: white; padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.opacity='0.9';" onmouseout="this.style.opacity='1';">
                                <i class="fas fa-times"></i> Close
                            </button>
                        </div>
                    `;
                }
            }
        }
        return headerActionsHtml;
    };

    const getInputAreaHtml = (ticket, internalToggleHtml) => {
        let inputAreaHtml = '';
        if(ticket.status === 'Resolved' || ticket.status === 'Closed') {
            const assignedAgentName = ticket.assigned_to_name ? ticket.assigned_to_name : 'an agent';
            let resTime = '';
            if (ticket.resolution_time) {
                const actionWord = ticket.status === 'Resolved' ? 'Solved' : 'Closed';
                resTime = `<div class="font-600 mt-2 mb-2" style="font-size: 13px; background: rgba(108, 76, 241, 0.1); color: #6C4CF1; display: inline-block; padding: 4px 12px; border-radius: 12px;"><i class="fas fa-stopwatch"></i> ${actionWord} by ${assignedAgentName} in: ${ticket.resolution_time}</div>`;
            }
            
            // Only user who created it (or admin) can re-open
            const isCreator = ticket.employee_id == IT_USER.emp_id;
            const isAdmin = isHelpdeskAdmin();
            let reopenBtn = '';
            
            if (ticket.status === 'Resolved' && (isCreator || isAdmin)) {
                reopenBtn = `<div class="mt-3"><button type="button" id="btn-reopen-ticket" style="border: 1px solid #EF4444; color: #EF4444; background: transparent; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='#EF4444'; this.style.color='#fff';" onmouseout="this.style.background='transparent'; this.style.color='#EF4444';"><i class="fas fa-undo"></i> Issue not fixed? Re-open Ticket</button></div>`;
            }

            inputAreaHtml = `
                <div class="p-4 text-center" style="background: #f8f9fa; border-top: 1px solid var(--it-border); border-radius: 0 0 16px 16px;">
                    <h6 style="color: #6C4CF1; font-weight: 700; margin-bottom: 4px;"><i class="fas fa-check-circle"></i> This ticket is ${ticket.status}.</h6>
                    ${resTime}
                    <div class="text-muted" style="font-size: 13px;">No further messages can be sent because the issue has been resolved.</div>
                    ${reopenBtn}
                </div>
            `;
        } else {
            const isCreator = ticket.employee_id == IT_USER.emp_id;
            const isAssignedAgent = ticket.assigned_to == IT_USER.emp_id;
            const isAdmin = isHelpdeskAdmin();
            
            if (isCreator || isAssignedAgent || isAdmin) {
                inputAreaHtml = `
                    <div class="it-chat-input-area">
                        ${internalToggleHtml}
                        <div style="width: 100%; display: flex; gap: 10px;">
                            <textarea id="chat-message-input" placeholder="Type a message..."></textarea>
                            <button class="it-chat-send-btn" id="btn-send-message"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </div>
                `;
            } else {
                inputAreaHtml = `
                    <div class="p-4 text-center text-muted" style="background: #f8f9fa; border-top: 1px solid var(--it-border); border-radius: 0 0 16px 16px; font-size: 13px;">
                        <i class="fas fa-lock mb-2" style="font-size: 20px; opacity: 0.5;"></i><br>
                        You must <strong>Claim</strong> this ticket to participate in the chat.
                    </div>
                `;
            }
        }
        return inputAreaHtml;
    };

    const renderTicketDetails = (ticket) => {
        lastMessageCount = ticket.messages.length;
        const isIT = IT_USER.is_it_staff;
        const internalToggleHtml = isIT ? `
            <div class="it-chat-internal-toggle">
                <input type="checkbox" id="is-internal-note">
                <label for="is-internal-note" class="m-0 cursor-pointer text-warning font-600">Mark as Internal Note (Hidden from employee)</label>
            </div>
        ` : '';

        const assignedText = ticket.assigned_to_name ? ticket.assigned_to_name : 'Unassigned';
        const assignedBadgeClass = ticket.assigned_to_name ? 'text-primary' : 'text-danger';
        
        // Header Actions (Claim, Handover, Resolve)
        let headerActionsHtml = getHeaderActionsHtml(ticket, isIT);

        let inputAreaHtml = getInputAreaHtml(ticket, internalToggleHtml);

        const profileImgUrl = ticket.profile_img ? ticket.profile_img : '../images/profile-image/default-avatar.svg';

        // Render Messages
        let messagesHtml = '';
        ticket.messages.forEach(m => {
            // If it's an old test "Ticket resolved by agent", skip it to clean the UI
            if (m.text.includes('Ticket resolved by agent.') || m.text.includes('Ticket closed by agent.')) return;

            if (m.is_system || m.text.startsWith('Ticket Re-opened.')) {
                messagesHtml += `
                    <div class="text-center my-3">
                        <span style="font-size: 11px; background: rgba(108, 76, 241, 0.1); color: #6C4CF1; padding: 6px 16px; border-radius: 20px; font-weight: 600; display: inline-block;">
                            <i class="fas fa-info-circle"></i> ${m.text}
                        </span>
                    </div>
                `;
            } else {
                const isRight = m.sender_id == IT_USER.emp_id; 
                const bubbleClass = isRight ? 'right' : 'left';
                const internalClass = m.is_internal ? 'it-internal-bubble' : '';
                const internalBadge = m.is_internal ? '<span class="it-internal-badge">Internal Note</span>' : '';

                // Show sender name if it's left bubble
                const senderNameHtml = !isRight ? `<div style="font-size: 10px; color: #a4b0be; margin-bottom: 2px; font-weight: 600;">${m.sender}</div>` : '';

                messagesHtml += `
                    <div class="it-chat-bubble ${bubbleClass} ${internalClass}">
                        ${senderNameHtml}
                        ${m.text}
                        <span class="it-chat-meta">${m.time} ${internalBadge}</span>
                    </div>
                `;
            }
        });

        const html = `
            <div class="it-email-view animate-fade">
                <!-- Ticket Header Info -->
                <div class="it-email-header">
                    <div class="it-email-subject" style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <button id="btn-back-to-list" style="background: transparent; border: none; font-size: 18px; color: var(--it-text-muted); cursor: pointer; padding: 0;"><i class="fas fa-arrow-left"></i></button>
                            <span>#${ticket.id} - ${ticket.subject}</span>
                        </div>
                        <div id="ticket-header-actions">${headerActionsHtml}</div>
                    </div>
                    <div class="it-email-meta-row mt-3">
                        <div class="it-email-sender-info">
                            <div class="it-sender-avatar" style="padding: 0; overflow: hidden; border: 2px solid #fff;">
                                <img src="${profileImgUrl}" alt="${ticket.user}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='../images/profile-image/default-avatar.svg'">
                            </div>
                            <div class="it-sender-details">
                                <h6 class="mb-1" style="font-size: 15px;">${ticket.user}</h6>
                                <div style="font-size: 12px; color: #636e72; display: flex; align-items: center; gap: 10px;">
                                    <span>Category: <strong>${ticket.category}</strong></span>
                                    <span style="color: #dfe6e9;">|</span>
                                    <span id="ticket-handled-by-badge" class="${assignedBadgeClass}">
                                        <i class="fas fa-user-shield"></i> Handled By: <strong id="ticket-handled-by-text">${assignedText}</strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="it-email-time-info">
                            <div class="text-muted" style="font-size: 13px;"><i class="far fa-calendar-alt"></i> ${ticket.date} at ${ticket.time}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Chat Area -->
                <div class="it-chat-container">
                    <div class="it-chat-messages" id="chat-messages-box">
                        ${messagesHtml}
                    </div>
                    
                    <!-- Input Area -->
                    <div id="ticket-input-area-wrapper">
                        ${inputAreaHtml}
                    </div>
                </div>
            </div>
        `;
        $('#main-content-area').html(html);
        scrollToBottom();
    };

    const showPlaceholder = () => {
        currentTicketId = null;
        lastMessageCount = 0;
        applyFilters();
        
        if (IT_USER.is_it_staff) {
            loadDashboard();
        } else {
            const html = `
                <div class="it-content-placeholder animate-fade">
                    <i class="fas fa-headset" style="font-size: 64px; margin-bottom: 15px; opacity: 0.2;"></i>
                    <h3>IT Support Helpdesk</h3>
                    <p>Track your support requests or create a new ticket for any technical issues.</p>
                </div>
            `;
            $('#main-content-area').html(html);
        }
    };

    const loadDashboard = () => {
        $('#main-content-area').html(`
            <div class="p-5 text-center text-muted animate-fade" style="margin-top: 100px;">
                <i class="fas fa-spinner fa-spin fa-2x mb-3" style="color: var(--it-primary);"></i>
                <p style="font-size: 14px; font-weight: 500;">Loading Helpdesk Dashboard Overview...</p>
            </div>
        `);
        
        $.ajax({
            url: apiUrl,
            method: 'GET',
            data: { action: 'get_dashboard_stats' },
            dataType: 'json',
            success: (res) => {
                if (res.success) {
                    renderDashboard(res.stats, res.top_resolvers);
                } else {
                    $('#main-content-area').html('<div class="p-5 text-center text-danger">Failed to load dashboard metrics.</div>');
                }
            },
            error: () => {
                $('#main-content-area').html('<div class="p-5 text-center text-danger">Server error while fetching dashboard statistics.</div>');
            }
        });
    };

    const renderDashboard = (stats, resolvers) => {
        const totalResolved = (stats.Resolved || 0) + (stats.Closed || 0);
        const totalTickets = stats.All || 0;
        const resolutionPercent = totalTickets > 0 ? Math.round((totalResolved / totalTickets) * 100) : 0;

        let resolversHtml = '';
        if (resolvers && resolvers.length > 0) {
            resolvers.forEach((r, idx) => {
                const colors = ['#6366F1', '#10B981', '#F59E0B', '#3B82F6', '#EF4444'];
                const color = colors[idx % colors.length];
                const initial = r.agent_name ? r.agent_name.charAt(0).toUpperCase() : 'A';
                
                let rankBadge = '';
                if (idx === 0) rankBadge = '<span style="font-size: 18px; margin-right: 4px;">🥇</span>';
                else if (idx === 1) rankBadge = '<span style="font-size: 18px; margin-right: 4px;">🥈</span>';
                else if (idx === 2) rankBadge = '<span style="font-size: 18px; margin-right: 4px;">🥉</span>';
                else rankBadge = `<span style="font-size: 12px; font-weight: 800; color: #94A3B8; margin-right: 8px; width: 20px; text-align: center;">#${idx+1}</span>`;
                
                const avatarHtml = r.profile_img 
                    ? `<img src="${r.profile_img}" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid ${color}40;">`
                    : `<img src="../images/profile-image/default-avatar.svg" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">`;
                
                resolversHtml += `
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: #F8FAFC; border: 1px solid #EEF2F6; border-radius: 12px; transition: all 0.2s;" class="hover-shadow">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            ${rankBadge}
                            ${avatarHtml}
                            <div>
                                <h6 style="margin: 0; font-weight: 700; font-size: 13px; color: #1E293B; max-width: 110px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${r.agent_name}</h6>
                                <span style="font-size: 10.5px; color: #64748B;">IT Agent</span>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 11px; font-weight: 700; color: #10B981; background: #E6FBF3; padding: 3px 8px; border-radius: 10px;">
                                ${r.resolved_count} Res.
                            </span>
                        </div>
                    </div>
                `;
            });
        } else {
            resolversHtml = `
                <div style="text-align: center; padding: 30px; color: #64748B; background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 12px;">
                    <i class="fas fa-medal" style="font-size: 32px; opacity: 0.3; margin-bottom: 8px; display: block;"></i>
                    <p style="margin: 0; font-size: 13px;">No tickets resolved yet. Time to claim some tickets!</p>
                </div>
            `;
        }
        
        const html = `
            <style>
                @keyframes pulse {
                    0%, 100% { opacity: 1; transform: scale(1); }
                    50% { opacity: .5; transform: scale(1.1); }
                }
                .dash-stat-card {
                    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                }
                .dash-stat-card:hover {
                    transform: translateY(-4px);
                }
                .dash-stat-card.card-all:hover {
                    border-color: #6366F1 !important;
                    box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.08) !important;
                }
                .dash-stat-card.card-open:hover {
                    border-color: #F59E0B !important;
                    box-shadow: 0 10px 20px -5px rgba(245, 158, 11, 0.08) !important;
                }
                .dash-stat-card.card-progress:hover {
                    border-color: #0EA5E9 !important;
                    box-shadow: 0 10px 20px -5px rgba(14, 165, 233, 0.08) !important;
                }
                .dash-stat-card.card-resolved:hover {
                    border-color: #10B981 !important;
                    box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.08) !important;
                }
                .dash-stat-card.card-closed:hover {
                    border-color: #64748B !important;
                    box-shadow: 0 10px 20px -5px rgba(100, 116, 139, 0.08) !important;
                }
                .hover-shadow {
                    transition: all 0.2s ease;
                }
                .hover-shadow:hover {
                    transform: translateX(4px);
                    background-color: #fff !important;
                    border-color: #CBD5E1 !important;
                    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
                }
            </style>
            
            <div class="it-dashboard-view animate-fade" style="padding: 0; font-family: inherit; flex: 1; display: flex; align-items: center; justify-content: center; width: 100%;">
                
                <div style="display: flex; flex-wrap: wrap; gap: 24px; width: 100%; max-width: 1200px; align-items: stretch; justify-content: center; margin: auto 0;">
                    
                    <!-- Left Column: Command Welcome, Stats & Success Rates -->
                    <div style="flex: 2; min-width: 320px; display: flex; flex-direction: column; gap: 24px;">
                        
                        <!-- Header Banner -->
                        <div style="background: linear-gradient(135deg, #F8FAFC, #EEF2F6); border: 1px solid #E2E8F0; padding: 22px 24px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
                            <div>
                                <h4 style="margin: 0; font-weight: 700; color: #0F172A; font-size: 20px; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-chart-line" style="color: #6C4CF1;"></i> Command Center
                                </h4>
                                <p style="margin: 4px 0 0 0; color: #475569; font-size: 13.5px; font-weight: 500;">Real-time ticket tracking, agent leaderboard, and performance metrics.</p>
                            </div>
                            <div style="background: #fff; border: 1px solid #E2E8F0; padding: 8px 16px; border-radius: 12px; display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #1E293B; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                                <span style="display: inline-block; width: 8px; height: 8px; background: #10B981; border-radius: 50%; animation: pulse 2s infinite;"></span>
                                <span id="dash-live-clock">--:--:--</span>
                            </div>
                        </div>
                        
                        <!-- Stats Grid -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(115px, 1fr)); gap: 16px;">
                            <!-- All Tickets -->
                            <div class="dash-stat-card card-all" style="background: #ffffff; border: 1px solid #E2E8F0; padding: 20px 16px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); cursor: pointer;" onclick="$('[data-filter=All]').click()">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <span style="font-size: 26px; font-weight: 800; color: #0F172A; line-height: 1;">${stats.All}</span>
                                    <div style="background: #EEF2FF; color: #4F46E5; width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-ticket-alt" style="font-size: 14px;"></i>
                                    </div>
                                </div>
                                <div style="font-size: 10.5px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">All Tickets</div>
                            </div>

                            <!-- Open -->
                            <div class="dash-stat-card card-open" style="background: #ffffff; border: 1px solid #E2E8F0; padding: 20px 16px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); cursor: pointer;" onclick="$('[data-filter=Open]').click()">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <span style="font-size: 26px; font-weight: 800; color: #0F172A; line-height: 1;">${stats.Open}</span>
                                    <div style="background: #FFF7ED; color: #D97706; width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-envelope-open-text" style="font-size: 14px;"></i>
                                    </div>
                                </div>
                                <div style="font-size: 10.5px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Open</div>
                            </div>

                            <!-- In Progress -->
                            <div class="dash-stat-card card-progress" style="background: #ffffff; border: 1px solid #E2E8F0; padding: 20px 16px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); cursor: pointer;" onclick="$('[data-filter=In-Progress]').click()">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <span style="font-size: 26px; font-weight: 800; color: #0F172A; line-height: 1;">${stats.In_Progress}</span>
                                    <div style="background: #F0F9FF; color: #0284C7; width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-spinner fa-spin" style="font-size: 14px;"></i>
                                    </div>
                                </div>
                                <div style="font-size: 10.5px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">In Progress</div>
                            </div>

                            <!-- Resolved -->
                            <div class="dash-stat-card card-resolved" style="background: #ffffff; border: 1px solid #E2E8F0; padding: 20px 16px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); cursor: pointer;" onclick="$('[data-filter=Resolved]').click()">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <span style="font-size: 26px; font-weight: 800; color: #0F172A; line-height: 1;">${stats.Resolved}</span>
                                    <div style="background: #ECFDF5; color: #059669; width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-check-double" style="font-size: 14px;"></i>
                                    </div>
                                </div>
                                <div style="font-size: 10.5px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Resolved</div>
                            </div>

                            <!-- Closed -->
                            <div class="dash-stat-card card-closed" style="background: #ffffff; border: 1px solid #E2E8F0; padding: 20px 16px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); cursor: pointer;" onclick="$('[data-filter=Closed]').click()">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <span style="font-size: 26px; font-weight: 800; color: #0F172A; line-height: 1;">${stats.Closed}</span>
                                    <div style="background: #F8FAFC; color: #475569; width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-folder-closed" style="font-size: 14px;"></i>
                                    </div>
                                </div>
                                <div style="font-size: 10.5px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">Closed</div>
                            </div>
                        </div>
                        
                        <!-- Performance Insights Panel -->
                        <div style="background: #fff; border: 1px solid #E2E8F0; border-radius: 16px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: center;">
                            <h5 style="margin: 0 0 16px 0; font-weight: 700; color: #1E293B; font-size: 14.5px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-hourglass-half" style="color: #6C4CF1;"></i> Helpdesk Resolution Rate
                            </h5>
                            
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <span style="font-size: 13.5px; font-weight: 600; color: #475569;">Overall Success Rate</span>
                                <span style="font-size: 15px; font-weight: 800; color: #10B981;">${resolutionPercent}%</span>
                            </div>
                            <div style="width: 100%; height: 8px; background: #F1F5F9; border-radius: 20px; overflow: hidden; margin-bottom: 20px;">
                                <div style="width: ${resolutionPercent}%; height: 100%; background: linear-gradient(90deg, #10B981, #059669); border-radius: 20px;"></div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div style="background: #F8FAFC; border: 1px solid #EEF2F6; padding: 12px 15px; border-radius: 12px; display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-check-circle" style="color: #10B981; font-size: 18px;"></i>
                                    <div>
                                        <span style="font-size: 10.5px; color: #64748B; display: block; font-weight: 600; text-transform: uppercase;">Completed Issues</span>
                                        <span style="font-size: 14.5px; font-weight: 700; color: #1E293B;">${stats.Resolved + stats.Closed}</span>
                                    </div>
                                </div>
                                
                                <div style="background: #F8FAFC; border: 1px solid #EEF2F6; padding: 12px 15px; border-radius: 12px; display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-exclamation-circle" style="color: #F59E0B; font-size: 18px;"></i>
                                    <div>
                                        <span style="font-size: 10.5px; color: #64748B; display: block; font-weight: 600; text-transform: uppercase;">Active In Queue</span>
                                        <span style="font-size: 14.5px; font-weight: 700; color: #1E293B;">${stats.Open + stats.In_Progress}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- Right Column: Leaderboard -->
                    <div style="flex: 1; min-width: 280px; display: flex; flex-direction: column;">
                        
                        <div style="background: #fff; border: 1px solid #E2E8F0; border-radius: 16px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                    <h5 style="margin: 0; font-weight: 700; color: #1E293B; font-size: 14.5px; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-trophy" style="color: #F59E0B;"></i> Helpdesk Stars
                                    </h5>
                                    <span style="font-size: 9.5px; font-weight: 700; color: #64748B; background: #F1F5F9; padding: 3px 8px; border-radius: 10px; text-transform: uppercase;">Rankings</span>
                                </div>
                                
                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                    ${resolversHtml}
                                </div>
                            </div>
                            
                            <div style="border-top: 1px solid #F1F5F9; padding-top: 15px; margin-top: 15px; text-align: center;">
                                <p style="margin: 0; font-size: 11px; color: #94A3B8; font-weight: 500;">
                                    <i class="fas fa-info-circle"></i> Resolved stats update live automatically.
                                </p>
                            </div>
                        </div>
                        
                    </div>
                    
                </div>
                
            </div>
        `;
        
        $('#main-content-area').html(html);
        
        // Start Live Clock inside Dashboard
        const updateClock = () => {
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            minutes = minutes < 10 ? '0'+minutes : minutes;
            seconds = seconds < 10 ? '0'+seconds : seconds;
            const strTime = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
            $('#dash-live-clock').text(strTime);
        };
        updateClock();
        if (window.dashClockInterval) clearInterval(window.dashClockInterval);
        window.dashClockInterval = setInterval(updateClock, 1000);
    };

    const showCreateForm = () => {
        currentTicketId = null;
        lastMessageCount = 0;
        applyFilters();
        const html = `
            <div class="it-create-ticket-view animate-fade">
                <h3 class="mb-4">Create New Support Ticket</h3>
                <form id="ticket-form" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create_ticket">
                    
                    <div class="it-form-group">
                        <label>Select Category</label>
                        <div class="it-category-tabs">
                            <div class="it-cat-tab active" data-cat="Hardware"><i class="fas fa-desktop"></i> Hardware</div>
                            <div class="it-cat-tab" data-cat="Software"><i class="fas fa-laptop-code"></i> Software</div>
                            <div class="it-cat-tab" data-cat="Network"><i class="fas fa-wifi"></i> Network</div>
                            <div class="it-cat-tab" data-cat="Access"><i class="fas fa-key"></i> Access/Login</div>
                            <div class="it-cat-tab" data-cat="Other"><i class="fas fa-ellipsis-h"></i> Other</div>
                        </div>
                        <input type="hidden" name="category" id="selected-category" value="Hardware">
                    </div>
                    
                    <div class="it-form-group" id="other-cat-group" style="display:none;">
                        <label>Specify Category</label>
                        <input type="text" name="custom_category" id="custom-category" class="form-control" placeholder="Enter custom category">
                    </div>
                    
                    <div class="it-form-group">
                        <label>Ticket Subject</label>
                        <input type="text" name="subject" class="form-control" required placeholder="Brief summary of the issue">
                    </div>
                    
                    <div class="it-form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="5" required placeholder="Provide detailed information about the problem"></textarea>
                    </div>
                    
                    <div class="it-form-actions">
                        <button type="button" class="it-btn-submit" id="btn-submit-ticket">Submit Ticket</button>
                        <button type="button" class="it-btn-cancel" id="btn-cancel-create">Cancel</button>
                    </div>
                </form>
            </div>
        `;
        $('#main-content-area').html(html);
    };

    const scrollToBottom = () => {
        const box = document.getElementById('chat-messages-box');
        if(box) box.scrollTop = box.scrollHeight;
    };

    return { init };
})();

$(document).ready(() => ITSupport.init());
