/**
 * LaraClassifier Images Thumbnails Clear Job Monitor
 *
 * Purpose: Monitors images thumbnails clearing jobs running in the background and shows notifications when complete
 *
 * How it works:
 * 1. User clicks "Clear Image Thumbnails" button
 * 2. Server queues the job and stores job ID in session
 * 3. Page loads with job ID in meta tag
 * 4. This script auto-detects the job ID and starts polling
 * 5. Polls server every 10 seconds to check job status
 * 6. When job completes, shows PNotify notification and stops polling
 *
 * Configuration:
 * - POLL_INTERVAL: How often to check job status (default: 10 seconds)
 * - MAX_POLLS: Maximum number of polling attempts (default: 30 = 5 minutes)
 *
 * Maintenance Notes:
 * - Job ID is stored in sessionStorage to persist across page reloads
 * - Status endpoint: /admin/actions/heavy/clear-image-thumbs/status?jobId=xxx
 * - Requires PNotify library to be loaded for notifications
 * - Queue worker must be running and listening to 'maintenance' queue
 */
(function () {
	'use strict';
	
	// ========================================================================
	// Configuration
	// ========================================================================
	
	const pollIntervalInSeconds = 10;
	const maxExecutionTime = 60 * 5; // 5 minutes (max)
	
	const POLL_INTERVAL = 1000 * pollIntervalInSeconds;         // Poll every {pollIntervalInSeconds} seconds
	const MAX_POLLS = maxExecutionTime / pollIntervalInSeconds; // Maximum {MAX_POLLS} polls
	
	// ========================================================================
	// State Variables
	// ========================================================================
	
	let pollCount = 0;       // Number of polls performed so far
	let pollSetInterval = null; // Interval ID for polling
	
	// ========================================================================
	// Helper Functions
	// ========================================================================
	
	/**
	 * Get the job ID from session storage or meta tag
	 *
	 * Job ID can come from two sources:
	 * 1. Meta tag (set by server when page loads after dispatching job)
	 * 2. SessionStorage (persists across page reloads)
	 *
	 * @returns {string|null} The job ID or null if not found
	 */
	function getJobId() {
		// First check sessionStorage (survives page reloads)
		const jobId = sessionStorage.getItem('clearImageThumbsJobId');
		if (jobId) {
			return jobId;
		}
		
		// Check if job ID is provided via meta tag
		const metaJobId = document.querySelector('meta[name="clear-image-thumbs-job-id"]');
		if (metaJobId) {
			return metaJobId.getAttribute('content');
		}
		
		return null;
	}
	
	/**
	 * Show PNotify notification to user
	 *
	 * @param {string} message - The message to display
	 * @param {string} type - Notification type: 'success', 'error', 'info', 'notice'
	 * @param {string} title - Optional title for the notification
	 */
	function showNotification(message, type, title = '') {
		// Check if PNotify library is loaded
		if (typeof PNotify === 'undefined') {
			console.error('PNotify is not loaded - cannot show notification');
			console.log('Message would have been:', message);
			return;
		}
		
		title = title || (type === 'success' ? 'Success' : type === 'error' ? 'Error' : 'Info');
		
		// Show the notification
		pnotifyAlertClient(type, message, title);
	}
	
	/**
	 * Check job status by polling the server
	 *
	 * Makes an AJAX request to the status endpoint and handles the response.
	 * Continues polling until job is completed, failed, or max polls reached.
	 *
	 * @param {string} jobId - The unique job ID to check
	 */
	function checkJobStatus(jobId) {
		const url = window.adminBaseUrl + '/actions/heavy/clear-image-thumbs/status?jobId=' + jobId;
		
		fetch(url, {
			method: 'GET',
			headers: {
				'X-Requested-With': 'XMLHttpRequest',
				'Accept': 'application/json',
			},
			credentials: 'same-origin'
		})
		.then(response => response.json())
		.then(data => {
			pollCount++;
			
			console.log('Images thumbnails clear job status:', data);
			
			// ================================================================
			// Handle job completion (success)
			// ================================================================
			if (data.status === 'completed') {
				showNotification(data.message, 'success', 'Images Thumbnails Cleared');
				stopPolling(jobId);
			}
			
			// ================================================================
			// Handle job failure
			// ================================================================
			else if (data.status === 'failed') {
				showNotification(data.message, 'error', 'Images Thumbnails Clear Failed');
				stopPolling(jobId);
			}
			
			// ================================================================
			// Handle job failure
			// ================================================================
			else if (data.status === 'not_found') {
				showNotification(data.message, 'error');
				stopPolling(jobId);
			}
			
			// ================================================================
			// Handle processing state
			// ================================================================
			else if (data.status === 'processing') {
				console.log('Images thumbnails clearing in progress...');
				// Continue polling (do nothing)
			}
			
			// ================================================================
			// Handle queued state
			// ================================================================
			else if (data.status === 'queued') {
				console.log('Images thumbnails clear job is queued...');
				// Continue polling (do nothing)
			}
			
			// ================================================================
			// Safety check: Stop polling if max attempts reached
			// ================================================================
			if (pollCount >= MAX_POLLS) {
				console.warn('Max polling attempts reached (5 minutes)');
				showNotification(
					'Images thumbnails clearing is taking longer than expected. Please check the queue worker logs.',
					'notice',
					'Still Processing'
				);
				stopPolling(jobId);
			}
		})
		.catch(error => {
			console.error('Error checking images thumbnails clear status:', error);
			
			// Only show error notification after several failed attempts
			// (to avoid false alarms from temporary network issues)
			if (pollCount > 3) {
				showNotification(
					'Failed to check images thumbnails clear status. Please refresh the page.',
					'error',
					'Connection Error'
				);
				stopPolling(jobId);
			}
		});
	}
	
	/**
	 * Stop polling and cleanup
	 *
	 * Clears the polling interval, removes job ID from storage,
	 * and cleans up the meta tag if present.
	 *
	 * @param {string} jobId - The job ID to clean up
	 */
	function stopPolling(jobId) {
		// Clear the polling interval if active
		if (pollSetInterval) {
			clearInterval(pollSetInterval);
			pollSetInterval = null;
		}
		
		// Remove job ID from sessionStorage
		sessionStorage.removeItem('clearImageThumbsJobId');
		
		// Remove meta tag if it exists
		const metaTag = document.querySelector('meta[name="clear-image-thumbs-job-id"]');
		if (metaTag) {
			metaTag.remove();
		}
		
		console.log('Stopped polling for images thumbnails clear job:', jobId);
	}
	
	/**
	 * Start polling for job status
	 *
	 * Initiates the polling process:
	 * 1. Checks status immediately
	 * 2. Then polls every POLL_INTERVAL milliseconds
	 *
	 * @param {string} jobId - The job ID to monitor
	 */
	function startPolling(jobId) {
		if (!jobId) {
			console.warn('No job ID provided for images thumbnails clear monitoring');
			return;
		}
		
		console.log('Starting images thumbnails clear job monitoring:', jobId);
		
		// Check immediately (don't wait for first interval)
		checkJobStatus(jobId);
		
		// Then poll at regular intervals
		pollSetInterval = setInterval(function () {
			checkJobStatus(jobId);
		}, POLL_INTERVAL);
	}
	
	/**
	 * Initialize monitoring on page load
	 *
	 * Called automatically when DOM is ready.
	 * Checks if there's a job to monitor and starts polling if found.
	 */
	function init() {
		// Wait for DOM to be ready
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', init);
			return;
		}
		
		// Check if there's an images thumbnails clear job to monitor
		const jobId = getJobId();
		
		if (jobId) {
			// Store in sessionStorage for persistence across page loads
			sessionStorage.setItem('clearImageThumbsJobId', jobId);
			
			// Start polling after a short delay to ensure PNotify is loaded
			// (PNotify is typically loaded after this script)
			setTimeout(function () {
				startPolling(jobId);
			}, 500);
		}
	}
	
	// ========================================================================
	// Auto-initialize
	// ========================================================================
	
	init();
	
	// ========================================================================
	// Public API
	// ========================================================================
	
	// Expose public methods for manual control if needed
	window.ImagesThumbnailsClearMonitor = {
		start: startPolling,
		stop: stopPolling,
		getJobId: getJobId
	};
})();
