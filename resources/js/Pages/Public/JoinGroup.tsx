import axios from "axios";
import React, { useCallback, useEffect, useState } from "react";
import { Helmet } from 'react-helmet-async';
import { Link, useParams } from 'react-router-dom';
import DownloadButtons from '../../Components/DownloadButtons';

const JoinGroup = () => {
  const { invitationCode } = useParams<{ invitationCode: string }>();
  const [copied, setCopied] = useState(false);
  const [groupName, setGroupData] = useState<string | null>(null);
  const [groupLoading, setGroupLoading] = useState(true);

  useEffect(() => {
    setGroupLoading(true);
    if (invitationCode) {
      fetchGroup();
    } else {
      setGroupLoading(false);
    }
  }, [invitationCode]);

  const fetchGroup = useCallback(async () => {
    try {
      console.log('Fetching group data for invitation code:', invitationCode);
      const response = await axios.get(`${import.meta.env.VITE_APP_URL}/api/public/groups/from-invitation/${invitationCode}`);
      if (response.status === 200) {
        setGroupData(response.data.name);
      } else {
        setGroupData('asd');
        // setGroupData(null);
      }
      setGroupLoading(false);
    }
    catch (error) {
      setGroupData('asd');
      setGroupLoading(false);
    }
  }, [invitationCode]);

  const copyInvitationCode = () => {
    if (groupName) {
      navigator.clipboard.writeText(invitationCode!)
        .then(() => {
          setCopied(true);
          setTimeout(() => setCopied(false), 2000);
        })
        .catch(err => {
          console.error('Failed to copy: ', err);
        });
    }
  };

  return (
    <div className="bg-gradient-to-br from-dodo-blue to-dodo-blue/80 flex flex-col justify-center grow shrink-0">
      <Helmet>
        <title>{groupName ? `Join ${groupName} on Dodo` : 'Dodo'}</title>
        <meta property="og:title" content={groupName ? `Join ${groupName} on Dodo` : 'Dodo'} />
        <meta property="og:type" content="website" />
        <meta property="og:url" content={window.location.href} />
        <meta property="og:image" content="https://www.dodoapp.net/assets/icon.png" />
        <meta property="og:description" content="Join this group on Dodo - The privacy-focused bill splitting app" />
      </Helmet>

      <div className="container mx-auto px-4 py-16 md:py-24 flex-1">
        <div className="max-w-4xl mx-auto">
          <div className="flex items-center justify-center mb-12">
            <img src="/assets/icon.png" className="h-20 w-20 mr-4" />
            <h1 className="text-5xl font-bold text-white">Dodo</h1>
          </div>

          {groupLoading ? (
            <div className="text-center bg-white rounded-xl p-8 shadow-lg">
              <h2 className="text-2xl font-bold text-gray-900 mb-4">Loading...</h2>
              <p className="text-gray-600 mb-8">Please wait while we fetch your group information.</p>
            </div>
          ) : ((!groupName || !invitationCode) ? (
            <div className="text-center bg-white rounded-xl p-8 shadow-lg">
              <h2 className="text-2xl font-bold text-gray-900 mb-4">Invalid or Expired Invitation</h2>
              <p className="text-gray-600 mb-8">This invitation link is no longer valid or has expired.</p>
              <Link
                to="/"
                className="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-dodo-blue hover:bg-dodo-blue/90"
              >
                Return Home
              </Link>
            </div>
          ) : (
            <div className="space-y-8">
              <div className="bg-white rounded-xl p-8 shadow-lg">
                <h2 className="text-2xl font-bold text-gray-900 mb-2">Join Group</h2>
                <p className="text-gray-600 mb-6">You've been invited to join {groupName} on Dodo</p>

                <a
                  href={`lenderapp://lenderapp/join/${invitationCode}`}
                  className="flex items-center justify-between w-full bg-dodo-blue text-white rounded-lg px-6 py-4 hover:bg-dodo-blue/90 transition-colors duration-300 mb-6"
                >
                  <div className="flex items-center">
                    <img src="/assets/icon.png" alt="Dodo" className="w-10 h-10 mr-4" />
                    <div>
                      <div className="text-sm opacity-90">Join group</div>
                      <div className="text-xl font-bold">{groupName}</div>
                    </div>
                  </div>
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" fill="currentColor">
                    <path d="M11,7L9.6,8.4l2.6,2.6H2v2h10.2l-2.6,2.6L11,17l5-5L11,7z" />
                  </svg>
                </a>

                <div className="flex items-center justify-between bg-gray-50 rounded-lg p-4">
                  <span className="text-gray-700">
                    Invitation code: <span className="font-mono font-medium">{invitationCode}</span>
                  </span>
                  <button
                    onClick={copyInvitationCode}
                    className="flex items-center text-dodo-blue hover:text-dodo-blue/80 transition-colors"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20" viewBox="0 0 24 24" fill="currentColor" className="mr-2">
                      <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z" />
                    </svg>
                    <span className="font-medium">{copied ? "Copied!" : "Copy"}</span>
                  </button>
                </div>
              </div>

              <div className="bg-white rounded-xl p-8 shadow-lg">
                <h3 className="text-xl font-bold text-gray-900 mb-6">Don't have Dodo yet?</h3>
                <p className="text-gray-600 mb-8">Download the app to start splitting bills with your friends securely.</p>
                <DownloadButtons showAll={true} />
              </div>
            </div>
          ))}
        </div>

        <footer className="mt-16 text-center text-white/80">
          <div className="flex justify-center space-x-6 mb-4">
            <a
              href="https://github.com/orgs/DevsWithDodo/repositories"
              target="_blank"
              rel="noopener noreferrer"
              className="hover:text-white transition-colors"
            >
              GitHub
            </a>
            <Link to="/privacy-policy" className="hover:text-white transition-colors">
              Privacy Policy
            </Link>
            <Link to="/" className="hover:text-white transition-colors">
              Home
            </Link>
          </div>
          <p className="text-sm">❤️ Made with love by two buddies who code together.</p>
        </footer>
      </div>
    </div>
  );
};

export default JoinGroup; 