const AdSlot = ({ slotKey, className = "" }) => (
  <div className={`ad-slot ${className}`} data-ad-slot={slotKey}>
    {/* AdSense units render via GoogleServices when enabled in admin */}
  </div>
);

export const HomeBelowHeroAd = () => <AdSlot slotKey="home_below_hero" className="section-padding pt-0" />;
export const BlogSidebarAd = () => <AdSlot slotKey="blog_sidebar" />;
export const BlogPostMidAd = () => <AdSlot slotKey="blog_post_mid" className="my-4" />;

export default AdSlot;
