/**
 * Global WebSockets Client (v5 - Clean Live Updates without Disruptive Full-Page Reloads)
 */

document.addEventListener('DOMContentLoaded', function () {
    if (typeof WebSocket === 'undefined') {
        console.warn('WebSockets not supported by this browser.');
        return;
    }

    const host = window.location.hostname || 'localhost';
    const port = window.wsPort || 6001;
    const wsUrl = `ws://${host}:${port}`;

    let ws = null;
    let reconnectTimeout = null;
    let reconnectAttempts = 0;
    const maxReconnectAttempts = 5;

    function connect() {
        if (reconnectTimeout) {
            clearTimeout(reconnectTimeout);
            reconnectTimeout = null;
        }

        try {
            ws = new WebSocket(wsUrl);
        } catch (e) {
            scheduleReconnect();
            return;
        }

        ws.onopen = () => {
            console.log('Connected to WebSocket server');
            reconnectAttempts = 0;
            if (window.currentUserId) {
                ws.send(JSON.stringify({
                    type: 'register',
                    employee_id: window.currentUserId
                }));
            }
        };

    const wsLastRun = {};
    const WS_COOLDOWN_MS = 60000; // Minimum 60 seconds between background WebSocket refetches

    function handleWSEventThrottled(type, callback) {
        const now = Date.now();
        const lastRun = wsLastRun[type] || 0;
        if (now - lastRun < WS_COOLDOWN_MS) {
            return; // Ignore frequent background triggers within 60 seconds
        }
        wsLastRun[type] = now;
        callback();
    }

    ws.onmessage = (event) => {
        try {
            const payload = JSON.parse(event.data);

            // Handle system / broadcast toasts
            if (payload.type === 'notification' || payload.type === 'announcement' || payload.type === 'broadcast') {
                if (typeof Toastify === 'function') {
                    Toastify({
                        text: `${payload.title ? payload.title + ': ' : ''}${payload.message}`,
                        duration: 5000,
                        close: true,
                        gravity: 'top',
                        position: 'right',
                        stopOnFocus: true,
                        style: {
                            background: payload.type === 'announcement' 
                                ? 'linear-gradient(to right, #4f46e5, #6366f1)' 
                                : 'linear-gradient(to right, #10b981, #059669)',
                            borderRadius: '8px',
                            boxShadow: '0 4px 12px rgba(0,0,0,0.15)'
                        }
                    }).showToast();
                }

                // Update Topbar Badge silently
                const notiBadge = document.getElementById('topbarNotiBadge');
                const sideBadge = document.getElementById('notiSidebarBadge');
                if (notiBadge) {
                    let count = parseInt(notiBadge.textContent) || 0;
                    count++;
                    notiBadge.textContent = count;
                    notiBadge.classList.remove('hidden');
                }
                if (sideBadge) {
                    let count = parseInt(sideBadge.textContent) || 0;
                    count++;
                    sideBadge.textContent = count;
                    sideBadge.classList.remove('hidden');
                }

                // Soft-refresh notification lists if on notifications page
                if (typeof window.fetchNotifications === 'function') {
                    handleWSEventThrottled('fetchNotifications', () => window.fetchNotifications());
                }
            }

            // Handle live attendance log updates
            if (payload.type === 'attendance_updated') {
                handleWSEventThrottled('attendance_updated', () => {
                    if (typeof window.refreshAttendanceStatus === 'function') {
                        window.refreshAttendanceStatus();
                    }
                    if (typeof window.refreshAttendanceTable === 'function') {
                        window.refreshAttendanceTable();
                    }
                    if (typeof window.fetchLogs === 'function') {
                        window.fetchLogs();
                    }
                    if (typeof window.fetchLog === 'function') {
                        window.fetchLog();
                    }
                    if (typeof window.fetchAdminDashboard === 'function') {
                        window.fetchAdminDashboard();
                    }
                    if (typeof window.fetchPersonalStats === 'function') {
                        window.fetchPersonalStats();
                    }
                });
            }

            // Handle announcements
            if (payload.type === 'announcements_updated') {
                handleWSEventThrottled('announcements_updated', () => {
                    if (typeof window.refreshAnnouncements === 'function') {
                        window.refreshAnnouncements();
                    }
                    if (typeof window.fetchAnnouncements === 'function') {
                        window.fetchAnnouncements();
                    }
                    if (typeof window.fetchLatestAnnouncements === 'function') {
                        window.fetchLatestAnnouncements();
                    }
                });
            }

            // Handle live calendar updates
            if (payload.type === 'calendar_updated') {
                handleWSEventThrottled('calendar_updated', () => {
                    if (typeof window.refreshCalendar === 'function') {
                        window.refreshCalendar();
                    }
                    if (typeof window.fetchEvents === 'function') {
                        window.fetchEvents();
                    }
                });
            }

            // Handle IT support tickets / chat updates
            if (payload.type === 'support_updated') {
                handleWSEventThrottled('support_updated', () => {
                    if (typeof window.loadTickets === 'function') {
                        window.loadTickets();
                    }
                    if (typeof window.loadTicketDetails === 'function' && window.activeTicketId) {
                        window.loadTicketDetails(window.activeTicketId);
                    }
                });
            }

            // Handle permission matrix updates
            if (payload.type === 'permissions_updated') {
                handleWSEventThrottled('permissions_updated', () => {
                    if (typeof window.pollPermissions === 'function') {
                        window.pollPermissions();
                    }
                });
            }

            // Handle live activity logs updates
            if (payload.type === 'activity_logged') {
                handleWSEventThrottled('activity_logged', () => {
                    if (typeof window.fetchActivityLogs === 'function') {
                        window.fetchActivityLogs();
                    }
                });
            }

            // Handle live job postings updates
            if (payload.type === 'jobs_updated') {
                handleWSEventThrottled('jobs_updated', () => {
                    if (typeof window.refreshJobPostings === 'function') {
                        window.refreshJobPostings();
                    }
                    if (typeof window.fetchJobs === 'function') {
                        window.fetchJobs();
                    }
                    if (typeof window.refreshCandidateDetail === 'function') {
                        window.refreshCandidateDetail();
                    }
                    if (typeof window.refreshInterviewsCalendar === 'function') {
                        window.refreshInterviewsCalendar();
                    }
                });
            }

            // Handle live candidates list updates
            if (payload.type === 'candidates_updated' || payload.type === 'walkin_updated') {
                handleWSEventThrottled('candidates_updated', () => {
                    if (typeof window.refreshCandidates === 'function') {
                        window.refreshCandidates();
                    }
                    if (typeof window.loadCandidates === 'function') {
                        window.loadCandidates();
                    }
                    if (typeof window.fetchWalkInCandidates === 'function') {
                        window.fetchWalkInCandidates();
                    }
                    if (typeof window.refreshCandidateDetail === 'function') {
                        window.refreshCandidateDetail();
                    }
                    if (typeof window.refreshInterviewsCalendar === 'function') {
                        window.refreshInterviewsCalendar();
                    }
                });
            }

            // Handle live calendar/interviews updates
            if (payload.type === 'interviews_updated') {
                handleWSEventThrottled('interviews_updated', () => {
                    if (typeof window.refreshCalendar === 'function') {
                        window.refreshCalendar();
                    }
                    if (typeof window.refreshInterviewsCalendar === 'function') {
                        window.refreshInterviewsCalendar();
                    }
                    if (typeof window.refreshCandidates === 'function') {
                        window.refreshCandidates();
                    }
                    if (typeof window.refreshCandidateDetail === 'function') {
                        window.refreshCandidateDetail();
                    }
                });
            }

            // Handle live leave request updates
            if (payload.type === 'leaves_updated') {
                handleWSEventThrottled('leaves_updated', () => {
                    if (typeof window.refreshLeaves === 'function') {
                        window.refreshLeaves();
                    }
                    if (typeof window.fetchLeaveRequests === 'function') {
                        window.fetchLeaveRequests();
                    }
                    if (typeof window.fetchAdminDashboard === 'function') {
                        window.fetchAdminDashboard();
                    }
                    if (typeof window.fetchHrDashboard === 'function') {
                        window.fetchHrDashboard();
                    }
                });
            }

            // Handle live policy updates
            if (payload.type === 'policies_updated') {
                handleWSEventThrottled('policies_updated', () => {
                    if (typeof window.refreshPolicies === 'function') {
                        window.refreshPolicies();
                    }
                });
            }

            // Handle live employee list updates
            if (payload.type === 'employees_updated') {
                handleWSEventThrottled('employees_updated', () => {
                    if (typeof window.fetchEmployees === 'function') {
                        window.fetchEmployees();
                    }
                    if (typeof window.fetchPendingOnboarding === 'function') {
                        window.fetchPendingOnboarding();
                    }
                    if (typeof window.fetchAdminDashboard === 'function') {
                        window.fetchAdminDashboard();
                    }
                    if (typeof window.fetchHrDashboard === 'function') {
                        window.fetchHrDashboard();
                    }
                });
            }

            // Handle live department updates
            if (payload.type === 'departments_updated') {
                handleWSEventThrottled('departments_updated', () => {
                    if (typeof window.fetchDepartments === 'function') {
                        window.fetchDepartments();
                    }
                });
            }

            // Handle live shift updates
            if (payload.type === 'shift_updated') {
                handleWSEventThrottled('shift_updated', () => {
                    if (typeof window.fetchShifts === 'function') {
                        window.fetchShifts();
                    }
                });
            }

            // Handle live hierarchy changes
            if (payload.type === 'hierarchy_updated') {
                handleWSEventThrottled('hierarchy_updated', () => {
                    if (typeof window.refreshHierarchy === 'function') {
                        window.refreshHierarchy();
                    }
                });
            }

            // Handle live KPI performance reviews updates
            if (payload.type === 'kpi_updated') {
                handleWSEventThrottled('kpi_updated', () => {
                    if (typeof window.refreshKpiSummary === 'function') {
                        window.refreshKpiSummary();
                    }
                    if (typeof window.refreshKpiTable === 'function') {
                        window.refreshKpiTable();
                    }
                    if (typeof window.refreshKpiReport === 'function') {
                        window.refreshKpiReport();
                    }
                });
            }

            // Handle live payroll updates
            if (payload.type === 'payroll_updated') {
                handleWSEventThrottled('payroll_updated', () => {
                    if (typeof window.refreshPayroll === 'function') {
                        window.refreshPayroll();
                    }
                    if (typeof window.fetchAdminDashboard === 'function') {
                        window.fetchAdminDashboard();
                    }
                    if (typeof window.fetchHrDashboard === 'function') {
                        window.fetchHrDashboard();
                    }
                });
            }

            // Handle live IT support ticket updates
            if (payload.type === 'support_updated') {
                handleWSEventThrottled('support_updated', () => {
                    if (typeof window.refreshITSupport === 'function') {
                        window.refreshITSupport();
                    }
                });
            }

            // Handle live leave quota updates
            if (payload.type === 'leave_types_updated') {
                handleWSEventThrottled('leave_types_updated', () => {
                    if (typeof window.refreshLeaveTypes === 'function') {
                        window.refreshLeaveTypes();
                    }
                });
            }
        } catch (e) {
            console.error('Error handling WebSocket message:', e);
        }
    };

        ws.onclose = () => {
            scheduleReconnect();
        };

        ws.onerror = (error) => {
            // Silence unhandled errors; onclose handles reconnect gracefully
        };
    }

    function scheduleReconnect() {
        if (reconnectTimeout) {
            clearTimeout(reconnectTimeout);
            reconnectTimeout = null;
        }
        if (reconnectAttempts < maxReconnectAttempts) {
            reconnectAttempts++;
            const delay = Math.min(3000 * Math.pow(1.5, reconnectAttempts - 1), 15000);
            reconnectTimeout = setTimeout(connect, delay);
        }
    }

    connect();
});
